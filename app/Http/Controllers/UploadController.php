<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class UploadController extends Controller
{
    public function showForm(): \Illuminate\View\View
    {
        $employeeTypes = DB::table('tb_typeemp')->get();
        return view('upload', compact('employeeTypes'));
    }

    public function upload(Request $request): \Illuminate\Http\RedirectResponse
    {
        // Validate input
        $request->validate([
            'employee_type' => 'required|integer|in:1,2',
            'file' => 'required|file|mimes:xlsx,xls|max:1048',
        ]);

        $employeeType = (int) $request->input('employee_type');
        $tableName = $employeeType === 1 ? 'temployee' : 'temployee_qf';

        if (!$request->hasFile('file')) {
            Log::error('❌ ไม่มีไฟล์ถูกอัปโหลดมาจากฟอร์ม');
            return back()->with('error', 'กรุณาเลือกไฟล์ Excel');
        }

        $uploadedFile = $request->file('file');

        Log::info('📤 รับไฟล์จากฟอร์มเรียบร้อย', [
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size_kb' => round($uploadedFile->getSize() / 1024, 2) . ' KB',
        ]);

        file_put_contents(storage_path('app/temp/test.txt'), 'Laravel can write to this folder!');
        Log::info('🧪 test.txt write result', [
            'exists' => file_exists(storage_path('app/temp/test.txt'))
        ]);

        $filename = time() . '_' . preg_replace('/\s+/', '_', $uploadedFile->getClientOriginalName());
        $destination = storage_path('app/temp/' . $filename);
        $uploadedFile->move(storage_path('app/temp'), $filename);

        Log::info('📦 move() result', [
            'path' => $destination,
            'exists' => file_exists($destination),
        ]);

        if (!file_exists($destination)) {
            return back()->with('error', 'ไม่สามารถอัปโหลดไฟล์ได้ กรุณาลองใหม่อีกครั้ง');
        }

        DB::beginTransaction();

        try {
            $allSheets = Excel::toCollection(null, $destination);
            Log::debug('📄 Sheets ทั้งหมด', ['sheet_count' => $allSheets->count()]);

            if ($allSheets->isEmpty() || $allSheets->first()->isEmpty()) {
                unlink($destination);
                return back()->with('error', 'ไม่พบข้อมูลในไฟล์ Excel หรือ Sheet แรกว่างเปล่า');
            }

            $collection = $allSheets->first();
            Log::debug('📋 แถวแรกที่อ่านได้', ['row0' => $collection[0] ?? null]);
            Log::debug('📊 จำนวนแถวทั้งหมด', ['row_count' => $collection->count()]);

            $successCount = 0;
            $failCount = 0;

            foreach ($collection as $index => $row) {
                if ($index === 0) continue; // ข้าม header
                if ((empty($row[0]) && empty($row[1])) || (!is_array($row) && !$row instanceof Collection)) {
                    continue;
                }

                $idx = isset($row[0]) ? trim((string) $row[0]) : null;
                $emailCmu = isset($row[1]) ? trim((string) $row[1]) : null;

                if (empty($idx) || empty($emailCmu)) {
                    Log::warning('⚠️ ข้ามแถว: idx หรือ email_cmu ว่าง', [
                        'row' => $index,
                        'row_data' => $row,
                    ]);
                    $failCount++;
                    continue;
                }

                try {
                    $query = DB::table($tableName)->where('idx', $idx);
                    if ($query->exists()) {
                        $query->update(['email_cmu' => $emailCmu]);
                        $successCount++;
                    } else {
                        Log::warning('⚠️ ไม่พบ idx นี้ในฐานข้อมูล', ['idx' => $idx]);
                        $failCount++;
                    }
                } catch (\Throwable $e) {
                    Log::error('❌ อัปเดตล้มเหลว', [
                        'idx' => $idx,
                        'error' => $e->getMessage(),
                    ]);
                    $failCount++;
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            if (file_exists($destination)) unlink($destination);
            Log::error('🔥 Fatal Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }

        // ✅ ลบไฟล์หลังประมวลผล
        if (file_exists($destination)) unlink($destination);

        $message = "✅ อัปเดตข้อมูลสำเร็จ {$successCount} รายการ, ไม่สำเร็จ {$failCount} รายการ";
        return back()->with($successCount > 0 ? 'success' : 'error', $message);
    }
}

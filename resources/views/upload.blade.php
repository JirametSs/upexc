@extends('layouts.main')

@section('title', 'อัปโหลดไฟล์ Excel')

@section('content')

<link rel="stylesheet" href="{{ asset('css/custom.css') }}" type="text/css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


@if (session('success'))
<div class="alert alert-success mt-3" id="upload-success">
    {{ session('success') }}
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const success = document.getElementById('upload-success');
        success.scrollIntoView({
            behavior: 'smooth'
        });
    });
</script>
@endif

@if (session('error'))
<div class="alert alert-danger mt-3">
    {{ session('error') }}
</div>
@endif

<div class="d-flex justify-content-center align-items-center min-vh-100">
    <div class="upload-container">
        <div class="upload-card">
            <div class="upload-header">
                <h4 class="mb-0">อัปโหลดไฟล์ Email CMU </h4>
                <p class="text-muted mb-0">ระบบบริหารจัดการข้อมูลพนักงาน</p><br>
                <b class="text-danger">การกำหนด format excel ต้องมี</b><br>
                <p class="text-danger">column1 = idx , column2 = email_cmu เท่านั้น</p>
            </div>

            <div class="upload-body">
                <form method="POST" action="{{ route('excel.upload') }}" enctype="multipart/form-data" id="upload-form">
                    @csrf

                    <div class="mb-4">
                        <label for="employee_type" class="form-label fw-semibold text-dark mb-2">
                            ประเภทพนักงาน <span class="text-danger">*</span>
                        </label>

                        <select name="employee_type" id="employee_type" class="form-select" required>
                            <option value="">-- กรุณาเลือกประเภทพนักงาน --</option>
                            @foreach ($employeeTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach

                        </select>
                        @error('employee_type')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ข้อมูลไม่ถูกต้อง',
                                    text: '{{ $message }}',
                                    confirmButtonColor: '#3aafa9',
                                    confirmButtonText: 'ตกลง'
                                });
                            });
                        </script>
                        @enderror
                    </div>
                    <div class="file-upload text-center p-5 border border-2 border-dashed rounded-3 bg-light" id="drop-area" style="cursor: pointer; transition: 0.3s;">
                        <input type="file" id="file" name="file" accept=".xlsx,.xls,.csv" class="d-none">

                        <label for="file" class="btn btn-outline-success mb-3 px-4">
                            <i class="fas fa-upload me-2"></i> เลือกไฟล์
                        </label>

                        <div class="file-info text-muted small" id="file-info">
                            ลากและวางไฟล์ที่นี่ หรือคลิกเพื่อเลือกไฟล์<br>
                            <span class="text-secondary">ไฟล์ที่อนุญาต: .xlsx, .xls, .csv | ขนาดสูงสุด 1MB</span>
                        </div>
                    </div>

                    @error('file')
                    <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <div class="progress d-none" id="upload-progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2" id="submit-btn">
                        อัปโหลดไฟล์
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/custom.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('file');
        const fileInfo = document.getElementById('file-info');
        const maxSize = 1 * 1024 * 1024; // 1MB

        /*** ตรวจทันทีเมื่อเลือกไฟล์ ***/
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file && file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไฟล์ใหญ่เกินไป',
                    text: 'กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 1MB',
                    confirmButtonColor: '#e3342f',
                    confirmButtonText: 'ตกลง'
                });

                this.value = '';
                fileInfo.innerHTML = `
                    ลากและวางไฟล์ที่นี่ หรือคลิกเพื่อเลือกไฟล์<br>
                    <span class="text-secondary">ไฟล์ที่อนุญาต: .xlsx, .xls, .csv | ขนาดสูงสุด 1MB</span>
                `;
            }
        });

        /*** ตรวจซ้ำเมื่อกด Submit ***/
        form.addEventListener('submit', function(e) {
            const file = fileInput.files[0];

            if (!file) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไม่พบไฟล์',
                    text: 'กรุณาเลือกไฟล์ก่อนอัปโหลด',
                    confirmButtonColor: '#e3342f',
                    confirmButtonText: 'ตกลง'
                });
                e.preventDefault();
                return;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'ไฟล์ใหญ่เกินไป',
                    text: 'กรุณาเลือกไฟล์ไม่เกิน 1MB',
                    confirmButtonColor: '#e3342f',
                    confirmButtonText: 'ตกลง'
                });

                fileInput.value = '';
                fileInfo.innerHTML = `
                    ลากและวางไฟล์ที่นี่ หรือคลิกเพื่อเลือกไฟล์<br>
                    <span class="text-secondary">ไฟล์ที่อนุญาต: .xlsx, .xls, .csv | ขนาดสูงสุด 1MB</span>
                `;
                e.preventDefault();
            }
        });
    });
</script>
@endpush


@endsection

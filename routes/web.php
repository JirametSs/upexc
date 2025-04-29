<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [UploadController::class, 'showForm'])->name('upload.page');

Route::get('/upload-excel', [UploadController::class, 'showForm'])->name('excel.form');

Route::post('/upload-excel', [UploadController::class, 'upload'])->name('excel.upload');

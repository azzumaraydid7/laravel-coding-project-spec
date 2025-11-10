<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;

use App\Http\Controllers\ProductController;

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/uploads', [FileUploadController::class, 'index'])->name('uploads.index');
Route::get('/uploads-csv', [FileUploadController::class, 'csv'])->name('uploads.csv');
Route::post('/uploads', [FileUploadController::class, 'store'])->name('uploads.store');
Route::post('/uploads/{upload}/resume', [FileUploadController::class, 'resume'])->name('uploads.resume');

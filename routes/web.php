<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (Authenticated Users Only)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/storage', [DashboardController::class, 'storage'])->name('dashboard.storage');
    Route::post('/dashboard/storage/buckets', [DashboardController::class, 'storeBucket'])->name('dashboard.storage.buckets.store');
    Route::post('/dashboard/storage/upload', [DashboardController::class, 'uploadObject'])->name('dashboard.storage.upload');
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');
});

Route::get('/test-ministack', function() {
    try {
        // Tulis file ke MiniStack S3 emulator
        Illuminate\Support\Facades\Storage::disk('s3')->put('test-file.txt', 'Halo dari Laravel ProjekAwan ke MiniStack!');
        
        // Ambil kembali file tersebut
        $content = Illuminate\Support\Facades\Storage::disk('s3')->get('test-file.txt');
        
        return "Integrasi Sukses! Isi file: " . $content;
    } catch (\Exception $e) {
        return "Integrasi Gagal. Pesan Eror: " . $e->getMessage();
    }
});
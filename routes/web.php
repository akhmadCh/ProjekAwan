<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResourceController;
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

// Protected Routes (Authenticated + Active Users Only)
Route::middleware(['auth', 'active'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Storage
    Route::get('/dashboard/storage', [DashboardController::class, 'storage'])->name('dashboard.storage');
    Route::post('/dashboard/storage/buckets', [DashboardController::class, 'storeBucket'])->name('dashboard.storage.buckets.store');
    Route::post('/dashboard/storage/upload', [DashboardController::class, 'uploadObject'])->name('dashboard.storage.upload');
    Route::delete('/dashboard/storage/delete/{id}', [DashboardController::class, 'deleteObject'])->name('dashboard.storage.objects.delete');
    Route::get('/dashboard/storage/download/{id}', [DashboardController::class, 'downloadObject'])->name('dashboard.storage.objects.download');
    
    // Subscription
    Route::get('/dashboard/subscription', [DashboardController::class, 'subscription'])->name('dashboard.subscription');
    Route::post('/dashboard/subscription/orders', [DashboardController::class, 'createSubscriptionOrder'])->name('dashboard.subscription.orders.store');
    Route::post('/dashboard/subscription/orders/sync', [DashboardController::class, 'syncSubscriptionOrder'])->name('dashboard.subscription.orders.sync');

    // Resources (Compute & Network)
    Route::get('/dashboard/resources', [ResourceController::class, 'index'])->name('dashboard.resources');
    Route::post('/dashboard/resources', [ResourceController::class, 'store'])->name('dashboard.resources.store');
    Route::get('/dashboard/resources/{id}', [ResourceController::class, 'show'])->name('dashboard.resources.show');
    Route::post('/dashboard/resources/{id}/start', [ResourceController::class, 'start'])->name('dashboard.resources.start');
    Route::post('/dashboard/resources/{id}/stop', [ResourceController::class, 'stop'])->name('dashboard.resources.stop');
});

// Midtrans Webhook (no CSRF, no auth)
Route::post('/midtrans/notification', [DashboardController::class, 'handleMidtransNotification'])->name('midtrans.notification');
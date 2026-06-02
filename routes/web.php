<?php

use App\Http\Controllers\GateAuthController;
use App\Http\Controllers\StudentTicketQrDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/students/{student}/ticket-qr.jpg', StudentTicketQrDownloadController::class)
    ->name('students.ticket-qr.download');

Route::prefix('gate')->name('gate.')->group(function (): void {
    Route::get('/login', [GateAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [GateAuthController::class, 'login'])->name('login.store');
});

Route::middleware('gate.access')->prefix('gate')->name('gate.')->group(function (): void {
    Route::get('/', [GateAuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/scan', [GateAuthController::class, 'scan'])->name('scan');
    Route::get('/stats', [GateAuthController::class, 'stats'])->name('stats');
    Route::get('/recent-scans', [GateAuthController::class, 'recentScans'])->name('recent-scans');
    Route::post('/logout', [GateAuthController::class, 'logout'])->name('logout');
});

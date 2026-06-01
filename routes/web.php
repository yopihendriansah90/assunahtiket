<?php

use App\Http\Controllers\StudentTicketQrDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/students/{student}/ticket-qr.jpg', StudentTicketQrDownloadController::class)
    ->name('students.ticket-qr.download');

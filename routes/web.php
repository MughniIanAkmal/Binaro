<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\RppController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect('/rpp');
});

Route::middleware(['auth'])->group(function () {
    // Modul RPP
    Route::prefix('rpp')->name('rpp.')->group(function () {
        Route::get('/', [RppController::class, 'index'])->name('index');
        Route::post('/', [RppController::class, 'store'])->name('store');
        Route::get('/{id}', [RppController::class, 'show'])->name('show');
        Route::put('/{id}', [RppController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [RppController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [RppController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [RppController::class, 'download'])->name('download');
    });

    // Modul Absensi
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/scan-qr', [AbsensiController::class, 'scanQr'])->name('scan-qr');
        Route::post('/manual', [AbsensiController::class, 'updateManual'])->name('manual');
        Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('destroy');
    });

    // Modul Mata Pelajaran
    Route::prefix('mapel')->name('mapel.')->group(function () {
        Route::get('/', [MapelController::class, 'index'])->name('index');
        Route::post('/', [MapelController::class, 'store'])->name('store');
        Route::put('/{id}', [MapelController::class, 'update'])->name('update');
        Route::delete('/{id}', [MapelController::class, 'destroy'])->name('destroy');
    });
});

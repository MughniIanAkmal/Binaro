<?php

use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Guru\AbsenScanController;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// ---------- Admin: kelola QR code siswa ----------
Route::middleware([EnsureAuthenticated::class, EnsureRole::class . ':admin'])->prefix('admin/qr-siswa')->name('admin.qr.')->group(function () {
    Route::get('/', [BarcodeController::class, 'index'])->name('index');
    Route::post('/buat-semua', [BarcodeController::class, 'storeAll'])->name('storeAll');
    Route::get('/cetak', [BarcodeController::class, 'cetakKelas'])->name('cetakKelas');

    Route::post('/{siswa}', [BarcodeController::class, 'store'])->name('store');
    Route::post('/{siswa}/buat-ulang', [BarcodeController::class, 'regenerate'])->name('regenerate');
    Route::get('/{siswa}/cetak', [BarcodeController::class, 'cetak'])->name('cetak');
});

// ---------- Guru & Admin: scan QR absensi ----------
Route::middleware([EnsureAuthenticated::class, EnsureRole::class . ':admin,guru'])->prefix('guru/absen')->name('guru.absen.')->group(function () {
    Route::get('/', [AbsenScanController::class, 'index'])->name('index');
    Route::post('/scan', [AbsenScanController::class, 'store'])->name('scan');
    Route::post('/izin', [AbsenScanController::class, 'storeIzin'])->name('izin');
});
<?php

use App\Http\Controllers\Admin\BarcodeController;
use App\Http\Controllers\Guru\AbsenScanController;
use App\Http\Middleware\EnsureAuthenticated;
use Illuminate\Support\Facades\Route;

/*
 * Tambahkan di paling bawah routes/web.php:
 *     require __DIR__.'/qr_absen.php';
 *
 * TODO: setelah login & role tim siap, tambahkan middleware,
 * misalnya ->middleware(['auth', 'admin']) dan ->middleware(['auth', 'guru']).
 */

// ---------- Admin: kelola QR code siswa ----------
Route::middleware([EnsureAuthenticated::class])->group(function () {
    Route::prefix('admin/qr-siswa')->name('admin.qr.')->group(function () {
        Route::get('/', [BarcodeController::class, 'index'])->name('index');
        Route::post('/buat-semua', [BarcodeController::class, 'storeAll'])->name('storeAll');
        Route::get('/cetak', [BarcodeController::class, 'cetakKelas'])->name('cetakKelas');

        Route::post('/{siswa}', [BarcodeController::class, 'store'])->name('store');
        Route::post('/{siswa}/buat-ulang', [BarcodeController::class, 'regenerate'])->name('regenerate');
        Route::get('/{siswa}/cetak', [BarcodeController::class, 'cetak'])->name('cetak');
    });

    Route::prefix('guru/absen')->name('guru.absen.')->group(function () {
        Route::get('/', [AbsenScanController::class, 'index'])->name('index');
        Route::post('/scan', [AbsenScanController::class, 'store'])->name('scan');
    });
});

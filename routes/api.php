<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Siswa API
    Route::prefix('siswa')->group(function () {
        Route::get('/profile', [SiswaApiController::class, 'profile']);
        Route::post('/profile', [SiswaApiController::class, 'updateProfile']);
        Route::get('/absensi-hari-ini', [SiswaApiController::class, 'todayAttendance']);
        Route::post('/absensi-scan', [SiswaApiController::class, 'selfScan']);
    });

    // Guru API
    Route::prefix('guru')->group(function () {
        Route::get('/profile', [GuruApiController::class, 'profile']);
        Route::post('/profile', [GuruApiController::class, 'updateProfile']);
        Route::get('/rpp', [GuruApiController::class, 'listRpp']);
        Route::post('/rpp', [GuruApiController::class, 'createRpp']);
        Route::put('/rpp/{id}', [GuruApiController::class, 'editRpp']);
        Route::delete('/rpp/{id}', [GuruApiController::class, 'deleteRpp']);
        Route::get('/siswa', [GuruApiController::class, 'listSiswa']);
        Route::post('/absensi/scan-qr', [GuruApiController::class, 'scanQr']);
        Route::post('/absen-massal', [GuruApiController::class, 'massAttendance']);
    });

    // Admin API
    Route::prefix('admin')->group(function () {
        Route::get('/absensi/rekap', [AdminApiController::class, 'rekapAbsensi']);
        Route::get('/qr/regenerate/{siswa_id}', [AdminApiController::class, 'regenerateQr']);
    });
});

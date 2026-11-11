<?php

use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\GuruApiController;
use App\Http\Controllers\Api\SiswaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication & Mobile Routes
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/logout', [AuthApiController::class, 'logout']);

// Mobile Dashboard & Profile (public, but will use token if present)
Route::get('/siswa/dashboard', [SiswaApiController::class, 'dashboard']);
Route::get('/siswa/profile', [SiswaApiController::class, 'profile']);
Route::post('/siswa/update-password', [SiswaApiController::class, 'updatePassword']);

// Mobile Mapel & Materi Learning Routes (public, token optional)
Route::get('/siswa/mapel', [SiswaApiController::class, 'mapelList']);
Route::get('/siswa/mapel/{id}/bab', [SiswaApiController::class, 'babList']);
Route::get('/siswa/bab/{id}/sub-bab', [SiswaApiController::class, 'subBabList']);
Route::get('/siswa/sub-bab/{id}/materi', [SiswaApiController::class, 'materiList']);
Route::get('/siswa/materi/{id}', [SiswaApiController::class, 'materiDetail']);
Route::get('/siswa/quiz/{id?}', [SiswaApiController::class, 'quizDetail']);
Route::post('/siswa/quiz/{id}/submit', [SiswaApiController::class, 'quizSubmit']);

// Mobile Ujian Online Routes (public, token optional)
Route::get('/siswa/ujian', [SiswaApiController::class, 'ujianIndex']);
Route::get('/siswa/ujian/{id}/petunjuk', [SiswaApiController::class, 'ujianPetunjuk']);
Route::get('/siswa/ujian/{id}/play', [SiswaApiController::class, 'ujianPlay']);
Route::post('/siswa/ujian/{id}/submit', [SiswaApiController::class, 'ujianSubmit']);
Route::get('/siswa/ujian/{id}/result', [SiswaApiController::class, 'ujianResult']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Siswa API (Authenticated)
    Route::prefix('siswa')->group(function () {
        Route::post('/profile', [SiswaApiController::class, 'updateProfile']);
        Route::get('/absensi-hari-ini', [SiswaApiController::class, 'todayAttendance']);
        Route::post('/absensi-scan', [SiswaApiController::class, 'selfScan']);
    });

    // Guru API (Authenticated)
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

    // Admin API (Authenticated)
    Route::prefix('admin')->group(function () {
        Route::get('/absensi/rekap', [AdminApiController::class, 'rekapAbsensi']);
        Route::get('/qr/regenerate/{siswa_id}', [AdminApiController::class, 'regenerateQr']);
    });
});

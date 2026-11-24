<?php

use App\Features\Controllers\Api\AdminApiController;
use App\Features\Controllers\Api\AuthApiController;
use App\Features\Controllers\Api\GuruApiController;
use App\Features\Controllers\Api\Siswa\AbsensiController;
use App\Features\Controllers\Api\Siswa\BabController;
use App\Features\Controllers\Api\Siswa\DashboardController;
use App\Features\Controllers\Api\Siswa\JadwalController;
use App\Features\Controllers\Api\Siswa\MapelController;
use App\Features\Controllers\Api\Siswa\MateriController;
use App\Features\Controllers\Api\Siswa\NotifikasiController;
use App\Features\Controllers\Api\Siswa\ProfileController;
use App\Features\Controllers\Api\Siswa\QuizController;
use App\Features\Controllers\Api\Siswa\SubBabController;
use App\Features\Controllers\Api\Siswa\UjianController;
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
Route::get('/siswa/dashboard', [DashboardController::class, 'dashboard']);
Route::get('/siswa/profile', [ProfileController::class, 'profile']);
Route::post('/siswa/update-password', [ProfileController::class, 'updatePassword']);

// Mobile Mapel & Materi Learning Routes (public, token optional)
Route::get('/siswa/mapel', [MapelController::class, 'mapelList']);
Route::get('/siswa/jadwal', [JadwalController::class, 'jadwalList']);
Route::get('/siswa/mapel/{id}/bab', [BabController::class, 'babList']);
Route::get('/siswa/bab/{id}/sub-bab', [SubBabController::class, 'subBabList']);
Route::get('/siswa/sub-bab/{id}/materi', [MateriController::class, 'materiList']);
Route::get('/siswa/materi/{id}', [MateriController::class, 'materiDetail']);
Route::get('/siswa/quiz/{id?}', [QuizController::class, 'quizDetail']);
Route::post('/siswa/quiz/{id}/submit', [QuizController::class, 'quizSubmit']);

// Mobile Ujian Online Routes (public, token optional)
Route::get('/siswa/ujian', [UjianController::class, 'ujianIndex']);
Route::get('/siswa/ujian/{id}/petunjuk', [UjianController::class, 'ujianPetunjuk']);
Route::get('/siswa/ujian/{id}/play', [UjianController::class, 'ujianPlay']);
Route::post('/siswa/ujian/{id}/submit', [UjianController::class, 'ujianSubmit']);
Route::get('/siswa/ujian/{id}/result', [UjianController::class, 'ujianResult']);

// Notifikasi (public, token optional)
Route::get('/siswa/notifikasi', [NotifikasiController::class, 'notifikasiList']);
Route::post('/siswa/notifikasi/baca', [NotifikasiController::class, 'notifikasiBaca']);

// Authenticated Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Siswa API (Authenticated)
    Route::prefix('siswa')->group(function () {
        Route::post('/profile', [ProfileController::class, 'updateProfile']);
        Route::get('/absensi-hari-ini', [AbsensiController::class, 'todayAttendance']);
        Route::post('/absensi-scan', [AbsensiController::class, 'selfScan']);
        Route::post('/foto-profil', [ProfileController::class, 'uploadFotoProfil']);
        Route::post('/foto-profil/hapus', [ProfileController::class, 'hapusFotoProfil']);
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

<?php

use App\Features\Controllers\Api\AdminApiController;
use App\Features\Controllers\Api\AuthApiController;
use App\Features\Controllers\Api\Guru\AbsensiController as GuruAbsensiController;
use App\Features\Controllers\Api\Guru\ProfileController as GuruProfileController;
use App\Features\Controllers\Api\Guru\RppController;
use App\Features\Controllers\Api\Guru\SiswaController as GuruSiswaController;
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

// Public Authentication
Route::post('/login', [AuthApiController::class, 'login']);
Route::post('/logout', [AuthApiController::class, 'logout']);

// Authenticated Routes (Sanctum + Role-Based Access Control)
Route::middleware('auth:sanctum')->group(function () {
    // Siswa API (Authenticated & Role: siswa)
    Route::middleware('api.role:siswa')->prefix('siswa')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'dashboard']);
        Route::get('/profile', [ProfileController::class, 'profile']);
        Route::post('/profile', [ProfileController::class, 'updateProfile']);
        Route::post('/update-password', [ProfileController::class, 'updatePassword']);
        Route::post('/foto-profil', [ProfileController::class, 'uploadFotoProfil']);
        Route::post('/foto-profil/hapus', [ProfileController::class, 'hapusFotoProfil']);

        Route::get('/mapel', [MapelController::class, 'mapelList']);
        Route::get('/jadwal', [JadwalController::class, 'jadwalList']);
        Route::get('/mapel/{id}/bab', [BabController::class, 'babList']);
        Route::get('/bab/{id}/sub-bab', [SubBabController::class, 'subBabList']);
        Route::get('/sub-bab/{id}/materi', [MateriController::class, 'materiList']);
        Route::get('/materi/{id}', [MateriController::class, 'materiDetail']);
        Route::get('/quiz/{id?}', [QuizController::class, 'quizDetail']);
        Route::post('/quiz/{id}/submit', [QuizController::class, 'quizSubmit']);

        Route::get('/ujian', [UjianController::class, 'ujianIndex']);
        Route::get('/ujian/{id}/petunjuk', [UjianController::class, 'ujianPetunjuk']);
        Route::get('/ujian/{id}/play', [UjianController::class, 'ujianPlay']);
        Route::post('/ujian/{id}/submit', [UjianController::class, 'ujianSubmit']);
        Route::get('/ujian/{id}/result', [UjianController::class, 'ujianResult']);

        Route::get('/notifikasi', [NotifikasiController::class, 'notifikasiList']);
        Route::post('/notifikasi/baca', [NotifikasiController::class, 'notifikasiBaca']);

        Route::get('/absensi-hari-ini', [AbsensiController::class, 'todayAttendance']);
        Route::post('/absensi-scan', [AbsensiController::class, 'selfScan']);
    });

    // Guru API (Authenticated & Role: guru)
    Route::middleware('api.role:guru')->prefix('guru')->group(function () {
        Route::get('/profile', [GuruProfileController::class, 'profile']);
        Route::post('/profile', [GuruProfileController::class, 'updateProfile']);
        Route::get('/rpp', [RppController::class, 'listRpp']);
        Route::post('/rpp', [RppController::class, 'createRpp']);
        Route::put('/rpp/{id}', [RppController::class, 'editRpp']);
        Route::delete('/rpp/{id}', [RppController::class, 'deleteRpp']);
        Route::get('/siswa', [GuruSiswaController::class, 'listSiswa']);
        Route::post('/absensi/scan-qr', [GuruAbsensiController::class, 'scanQr']);
        Route::post('/absen-massal', [GuruAbsensiController::class, 'massAttendance']);
    });

    // Admin API (Authenticated & Role: admin)
    Route::middleware('api.role:admin')->prefix('admin')->group(function () {
        Route::get('/absensi/rekap', [AdminApiController::class, 'rekapAbsensi']);
        Route::get('/qr/regenerate/{siswa_id}', [AdminApiController::class, 'regenerateQr']);
    });
});

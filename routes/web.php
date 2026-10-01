<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\MateriController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\RppController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaLearningController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\Guru\NotifikasiPrController;
use App\Http\Middleware\EnsureAuthenticated;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    $type = session('user_type');
    if ($type === 'admin') return redirect('/admin/dashboard');
    if ($type === 'siswa') return redirect('/siswa/dashboard');
    return redirect('/guru/dashboard');
});

Route::get('/dashboard', function () {
    if (!session()->has('user_id')) {
        return redirect('/login');
    }
    $type = session('user_type');
    if ($type === 'admin') return redirect('/admin/dashboard');
    if ($type === 'siswa') return redirect('/siswa/dashboard');
    return redirect('/guru/dashboard');
})->name('dashboard');

Route::middleware([EnsureAuthenticated::class])->group(function () {
    // 1. RPP (Modul Ajar) - Admin & Guru
    Route::middleware([EnsureRole::class . ':admin,guru'])->prefix('rpp')->name('rpp.')->group(function () {
        Route::get('/', [RppController::class, 'index'])->name('index');
        Route::post('/', [RppController::class, 'store'])->name('store');
        Route::get('/{id}', [RppController::class, 'show'])->name('show');
        Route::put('/{id}', [RppController::class, 'update'])->name('update');
        Route::patch('/{id}/status', [RppController::class, 'updateStatus'])->name('updateStatus');
        Route::delete('/{id}', [RppController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/download', [RppController::class, 'download'])->name('download');
    });

    // 2. Absensi Harian - Admin & Guru
    Route::middleware([EnsureRole::class . ':admin,guru'])->prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('index');
        Route::post('/scan-qr', [AbsensiController::class, 'scanQr'])
            ->middleware(EnsureGuruOrAdmin::class)
            ->name('scan-qr');
        Route::post('/manual', [AbsensiController::class, 'updateManual'])->name('manual');
        Route::delete('/{id}', [AbsensiController::class, 'destroy'])->name('destroy');
        Route::post('/settings', [AbsensiController::class, 'updateSettings'])->name('settings');
    });

    // 3. Kelola Mata Pelajaran - Admin & Guru
    Route::middleware([EnsureRole::class . ':admin,guru'])->prefix('mapel')->name('mapel.')->group(function () {
        Route::get('/', [MapelController::class, 'index'])->name('index');
        Route::post('/', [MapelController::class, 'store'])->name('store');
        Route::put('/{id}', [MapelController::class, 'update'])->name('update');
        Route::delete('/{id}', [MapelController::class, 'destroy'])->name('destroy');
    });

    // 4. Guru Dashboard & Pembelajaran (Role: Guru)
    Route::middleware([EnsureRole::class . ':guru'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [GuruController::class, 'dashboard'])->name('dashboard');
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');

        // Materi Management & Learning Hierarchy (PRD 4.1)
        Route::get('/mapel-belajar', [MateriController::class, 'mapelGrid'])->name('mapel.browse');
        Route::get('/mapel-belajar/{idMapel}/bab', [MateriController::class, 'babList'])->name('bab.index');
        Route::get('/bab/{idBab}/sub-bab', [MateriController::class, 'subBabList'])->name('sub_bab.index');
        Route::get('/sub-bab/{idSubBab}/materi', [MateriController::class, 'materiList'])->name('materi.sub_bab');

        Route::prefix('materi')->name('materi.')->group(function () {
            Route::get('/', [MateriController::class, 'index'])->name('index');
            Route::post('/bab', [MateriController::class, 'storeBab'])->name('store.bab');
            Route::post('/sub-bab', [MateriController::class, 'storeSubBab'])->name('store.sub-bab');
            Route::post('/store', [MateriController::class, 'storeMateri'])->name('store');
            Route::put('/{id}', [MateriController::class, 'updateMateri'])->name('update');
            Route::delete('/{id}', [MateriController::class, 'destroyMateri'])->name('destroy');
            Route::delete('/bab/{id}', [MateriController::class, 'destroyBab'])->name('destroy.bab');
            Route::delete('/sub-bab/{id}', [MateriController::class, 'destroySubBab'])->name('destroy.sub-bab');
        });

        // Cascading Dropdown API Helpers for Modal Action (PRD 4.2)
        Route::prefix('api/cascading')->name('cascading.')->group(function () {
            Route::get('/babs/{idMapel}', [MateriController::class, 'getBabsByMapel'])->name('babs');
            Route::get('/sub-babs/{idBab}', [MateriController::class, 'getSubBabsByBab'])->name('sub_babs');
            Route::get('/materis/{idSubBab}', [MateriController::class, 'getMaterisBySubBab'])->name('materis');
            Route::get('/materi/{idMateri}', [MateriController::class, 'getMateriDetail'])->name('materi_detail');
        });

        // Quiz Management
        Route::prefix('quiz')->name('quiz.')->group(function () {
            Route::get('/{idQuiz}/bank', [QuizController::class, 'bankSoal'])->name('bank');
            Route::post('/{idQuiz}/soal', [QuizController::class, 'storeSoal'])->name('store.soal');
            Route::delete('/soal/{idSoal}', [QuizController::class, 'destroySoal'])->name('destroy.soal');
            Route::post('/{idQuiz}/import', [QuizController::class, 'importSoal'])->name('import');
            Route::get('/template/download', [QuizController::class, 'downloadTemplate'])->name('template.download');
            Route::get('/rekap', [QuizController::class, 'rekap'])->name('rekap');
            Route::get('/export/{idQuiz}', [QuizController::class, 'exportRekap'])->name('export');
        });

        // Kelola Notifikasi & Tugas PR (CRUD + Kirim Notifikasi ke Siswa)
        Route::prefix('notifikasi-pr')->name('notifikasi_pr.')->group(function () {
            Route::get('/', [NotifikasiPrController::class, 'index'])->name('index');
            Route::get('/create', [NotifikasiPrController::class, 'create'])->name('create');
            Route::post('/', [NotifikasiPrController::class, 'store'])->name('store');
            Route::get('/{id}', [NotifikasiPrController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [NotifikasiPrController::class, 'edit'])->name('edit');
            Route::put('/{id}', [NotifikasiPrController::class, 'update'])->name('update');
            Route::delete('/{id}', [NotifikasiPrController::class, 'destroy'])->name('destroy');
            Route::post('/kirim', [NotifikasiPrController::class, 'kirim'])->name('kirim');
            Route::delete('/notifikasi/{id}', [NotifikasiPrController::class, 'destroyNotifikasi'])->name('destroy_notifikasi');
        });
    });

    // 5. Akun Guru CRUD (Role: Admin)
    Route::middleware([EnsureRole::class . ':admin'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/', [GuruController::class, 'index'])->name('index');
        Route::get('/create', [GuruController::class, 'create'])->name('create');
        Route::post('/', [GuruController::class, 'store'])->name('store');
        Route::get('/{guru}/edit', [GuruController::class, 'edit'])->name('edit');
        Route::put('/{guru}', [GuruController::class, 'update'])->name('update');
        Route::delete('/{guru}', [GuruController::class, 'destroy'])->name('destroy');
    });

    // 6. Siswa Dashboard & Learning Engine (Role: Siswa)
    Route::middleware([EnsureRole::class . ':siswa'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/dashboard', [SiswaController::class, 'dashboard'])->name('dashboard');
        Route::get('/profil', [SiswaController::class, 'profile'])->name('profile');
        Route::put('/profil', [SiswaController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [SiswaController::class, 'updatePassword'])->name('password.update');
        Route::get('/mapel', [SiswaLearningController::class, 'mapelIndex'])->name('mapel.index');
        Route::get('/mapel/{idMapel}', [SiswaLearningController::class, 'materiIndex'])->name('materi.index');
        Route::get('/sub-bab/{idSubBab}/materi', [SiswaLearningController::class, 'subBabMateri'])->name('sub_bab.materi');
        Route::get('/materi/{idMateri}', [SiswaLearningController::class, 'viewMateri'])->name('materi.view');

        // Ujian Online Siswa (Sahabat Belajar)
        Route::get('/ujian', [SiswaLearningController::class, 'daftarUjian'])->name('ujian.index');
        Route::get('/ujian/{idQuiz}/petunjuk', [SiswaLearningController::class, 'petunjukUjian'])->name('ujian.petunjuk');

        Route::get('/quiz/{idQuiz}/play', [SiswaLearningController::class, 'playQuiz'])->name('quiz.play');
        Route::post('/quiz/{idQuiz}/submit', [SiswaLearningController::class, 'submitQuiz'])->name('quiz.submit');
        Route::get('/quiz/{idQuiz}/result', [SiswaLearningController::class, 'resultQuiz'])->name('quiz.result');
    });

    // 7. Akun Siswa CRUD (Role: Admin & Guru)
    Route::middleware([EnsureRole::class . ':admin,guru'])->prefix('siswa')->name('siswa.')->group(function () {
        Route::get('/', [SiswaController::class, 'index'])->name('index');
        Route::get('/create', [SiswaController::class, 'create'])->name('create');
        Route::post('/', [SiswaController::class, 'store'])->name('store');
        Route::get('/{siswa}/edit', [SiswaController::class, 'edit'])->name('edit');
        Route::put('/{siswa}', [SiswaController::class, 'update'])->name('update');
        Route::delete('/{siswa}', [SiswaController::class, 'destroy'])->name('destroy');
    });

    // 8. Admin Dashboard (Role: Admin)
    Route::middleware([EnsureRole::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/', function() { return redirect()->route('admin.dashboard'); });
        Route::get('/absensi/rekap', [AbsensiController::class, 'rekap'])->name('absensi.rekap');
    });

    // 9. Jadwal (Role: Admin & Guru)
    Route::middleware([EnsureRole::class . ':admin,guru'])->prefix('jadwal')->name('jadwal.')->group(function () {
        Route::get('/', [JadwalController::class, 'index'])->name('index');
        Route::get('/create', [JadwalController::class, 'create'])->name('create');
        Route::post('/', [JadwalController::class, 'store'])->name('store');
        Route::get('/{jadwal}/edit', [JadwalController::class, 'edit'])->name('edit');
        Route::put('/{jadwal}', [JadwalController::class, 'update'])->name('update');
        Route::delete('/{jadwal}', [JadwalController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/qr_absen.php';

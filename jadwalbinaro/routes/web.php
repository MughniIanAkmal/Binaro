<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JadwalController;

// Halaman utama langsung ke jadwal
Route::get('/', function () {
    return redirect('/jadwal');
});

// Route Jadwal Pelajaran (Fitur Utama Kamu)
Route::resource('jadwal', JadwalController::class);

// Route Dummy untuk Sidebar (Mencegah Error Route Not Found)
Route::get('/mapel', function () { return view('jadwal.index'); })->name('mapel.index');
Route::get('/rpp', function () { return view('jadwal.index'); })->name('rpp.index');
Route::get('/absensi', function () { return view('jadwal.index'); })->name('absensi.index');

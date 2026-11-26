<?php

namespace App\Policies;

use App\Features\Models\Absen;
use Illuminate\Support\Facades\Session;

/**
 * Policy untuk model Absen.
 *
 * Semua operasi absensi dibatasi oleh role middleware (admin/guru),
 * namun policy ini memberi perlindungan tambahan level record agar
 * pengguna tidak bisa memanipulasi ID resource yang bukan miliknya.
 */
class AbsenPolicy
{
    /**
     * Dapatkan tipe user saat ini dari session.
     */
    private function userType(): ?string
    {
        return Session::get('user_type');
    }

    /**
     * Dapatkan ID user saat ini dari session.
     */
    private function userId(): ?int
    {
        $userId = Session::get('user_id');

        return is_numeric($userId) ? (int) $userId : null;
    }

    /**
     * Tentukan apakah user dapat melihat/mengelola semua list absen.
     *
     * Hanya admin dan guru yang memiliki akses (siswa hanya membaca
     * data diri sendiri lewat API).
     */
    public function viewAny(): bool
    {
        return in_array($this->userType(), ['admin', 'guru'], true);
    }

    /**
     * Tentukan apakah user dapat melihat satu record absen.
     *
     * Admin bebas melihat semua. Guru hanya boleh melihat absensi
     * siswa yang menjadi tanggung jawabnya (yang dicatat/dimiliki).
     */
    public function view(Absen $absen): bool
    {
        // Admin selalu punya akses
        if ($this->userType() === 'admin') {
            return true;
        }

        // Guru hanya bisa melihat absen yang mereka catat atau
        // yang berhubungan dengan siswanya.
        $guruId = $this->userId();
        if ($guruId && $absen->id_guru === $guruId) {
            return true;
        }

        return false;
    }

    /**
     * Tentukan apakah user dapat membuat record absen baru.
     *
     * Admin dan guru berwenang mencatat absensi.
     */
    public function create(): bool
    {
        return in_array($this->userType(), ['admin', 'guru'], true);
    }

    /**
     * Tentukan apakah user dapat memperbarui record absen.
     *
     * Admin bebas mengubah. Guru hanya bisa mengubah absen yang
     * mereka catat sendiri.
     */
    public function update(Absen $absen): bool
    {
        // Admin selalu punya akses
        if ($this->userType() === 'admin') {
            return true;
        }

        $guruId = $this->userId();

        return (bool) ($guruId && $absen->id_guru === $guruId);
    }

    /**
     * Tentukan apakah user dapat menghapus record absen.
     *
     * Hanya admin yang berwenang menghapus record absensi.
     * Guru tidak diizinkan menghapus demi integritas data.
     */
    public function delete(Absen $absen): bool
    {
        return $this->userType() === 'admin';
    }
}

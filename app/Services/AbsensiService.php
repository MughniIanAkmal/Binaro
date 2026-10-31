<?php

namespace App\Services;

use App\Models\Absen;
use App\Models\AbsensiSetting;
use App\Models\Barcode;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Database\QueryException;

class AbsensiService
{
    /**
     * Pola toleransi payload QR: hasil scan kadang membawa karakter
     * tambahan (URL, newline, spasi) di sekitar kode kartu siswa.
     */
    private const POLA_KODE_QR = '/\b((?:QR|BIN)-[A-Z0-9-]{4,40})\b/';

    /**
     * ID guru pencatat absensi: guru yang sedang login (sesi),
     * lalu nilai dari request jika valid, terakhir guru pertama
     * agar kolom id_guru yang NOT NULL tetap terisi.
     */
    public function guruPencatatId(?int $requestId = null): ?int
    {
        if (session('user_type') === 'guru' && session('user_id')) {
            return (int) session('user_id');
        }

        if ($requestId && Guru::where('id_guru', $requestId)->exists()) {
            return $requestId;
        }

        return Guru::query()->value('id_guru');
    }

    /**
     * Keterangan kehadiran berdasarkan jam scan dan pengaturan
     * batas jam absensi (batas_awal / batas_tepat / batas_tutup).
     */
    public function keteranganWaktu(?string $jam = null): string
    {
        $jam = $jam ?? now()->format('H:i');
        $batasAwal = AbsensiSetting::get('batas_awal', '07:00');
        $batasTepat = AbsensiSetting::get('batas_tepat', '08:00');
        $batasTutup = AbsensiSetting::get('batas_tutup', '12:00');

        if ($jam > $batasTutup) {
            return 'Terlambat';
        }

        if ($jam < $batasAwal) {
            return 'Datang Lebih Awal';
        }

        if ($jam <= $batasTepat) {
            return 'Tepat Waktu';
        }

        return 'Terlambat';
    }

    /**
     * Mencatat presensi dari satu kali scan/ketik kode QR siswa.
     * Satu-satunya tempat aturan absensi scan: toleransi kode,
     * batas jam, anti-duplikat, dan peningkatan Alpa otomatis
     * menjadi Hadir. Dipakai bersama oleh halaman web dan API.
     *
     * Struktur hasil:
     * - hasil         : 'ok' | 'duplikat' | 'tidak_dikenal'
     * - message       : pesan siap tampil untuk pengguna
     * - siswa         : model Siswa (null jika kode tidak dikenal)
     * - absen         : model Absen (null jika tidak dikenal/duplikat)
     * - jam           : jam pencatatan format 'H:i'
     * - statusTampil  : 'Hadir' | 'Terlambat' (untuk badge UI)
     * - keterangan    : teks keterangan yang tersimpan
     * - upgrade       : true jika Alpa otomatis ditingkatkan jadi Hadir
     */
    public function catatScan(string $kode, ?int $idGuru = null, string $metode = 'scan_qr'): array
    {
        $kode = strtoupper(trim($kode));
        $barcode = Barcode::with('siswa.kelas')->where('kode_barcode', $kode)->first();

        if (! $barcode && preg_match(self::POLA_KODE_QR, $kode, $m)) {
            $barcode = Barcode::with('siswa.kelas')->where('kode_barcode', $m[1])->first();
        }

        if (! $barcode || ! $barcode->siswa) {
            return [
                'hasil' => 'tidak_dikenal',
                'message' => 'Kode tidak dikenal. Pastikan kartu siswa masih berlaku.',
                'siswa' => null,
                'absen' => null,
            ];
        }

        $siswa = $barcode->siswa;
        $today = today()->toDateString();
        $waktu = now();
        $metode = in_array($metode, ['scan_qr', 'manual_guru'], true) ? $metode : 'scan_qr';

        $existing = Absen::where('id_siswa', $siswa->id_siswa)
            ->whereDate('tanggal', $today)
            ->first();

        if ($existing && $existing->status !== 'Alpa') {
            return [
                'hasil' => 'duplikat',
                'message' => "{$siswa->nm_siswa} sudah absen hari ini.",
                'siswa' => $siswa,
                'absen' => $existing,
            ];
        }

        $keterangan = $this->keteranganWaktu($waktu->format('H:i'));
        $idGuru = $idGuru ?? $this->guruPencatatId();

        if ($existing) {
            // Scan susulan: Alpa otomatis ditingkatkan menjadi Hadir.
            $existing->update([
                'id_guru' => $idGuru,
                'id_barcode' => $barcode->id_barcode,
                'metode' => $metode,
                'status' => 'Hadir',
                'keterangan' => $keterangan,
                'waktu_absen' => $waktu,
            ]);
            $absen = $existing;
            $message = "Hadir: {$siswa->nm_siswa} (Alpa otomatis diperbarui)";
        } else {
            $absen = Absen::create([
                'id_guru' => $idGuru,
                'id_siswa' => $siswa->id_siswa,
                'id_barcode' => $barcode->id_barcode,
                'metode' => $metode,
                'status' => 'Hadir',
                'keterangan' => $keterangan,
                'tanggal' => $today,
                'waktu_absen' => $waktu,
            ]);
            $message = "Hadir: {$siswa->nm_siswa}";
        }

        return [
            'hasil' => 'ok',
            'message' => $message,
            'siswa' => $siswa,
            'absen' => $absen,
            'jam' => $waktu->format('H:i'),
            'statusTampil' => $keterangan === 'Terlambat' ? 'Terlambat' : 'Hadir',
            'keterangan' => $keterangan,
            'upgrade' => (bool) $existing,
        ];
    }

    /**
     * Alpha otomatis: siswa tanpa baris absen pada $tanggal
     * dianggap Alpa dan dibuatkan record Alpa.
     *
     * Aturan:
     * - tanggal future ( > hari ini ) -> dilewati, return 0.
     * - tanggal lalu ( < hari ini ) -> langsung dibuatkan Alpa.
     * - tanggal hari ini -> hanya dibuatkan Alpa jika sudah
     *   melewati batas_tutup absensi (default 12:00), agar siswa
     *   yang datang terlambat masih bisa scan QR.
     *
     * @return int jumlah record Alpa otomatis yang dibuat
     */
    public function ensureAlphaOtomatis(string $tanggal): int
    {
        $today = now()->toDateString();

        if ($tanggal > $today) {
            return 0;
        }

        if ($tanggal === $today && now()->format('H:i') <= AbsensiSetting::get('batas_tutup', '12:00')) {
            return 0;
        }

        $existingIds = Absen::whereDate('tanggal', $tanggal)->pluck('id_siswa')->toArray();

        $missing = Siswa::when(! empty($existingIds), fn ($q) => $q->whereNotIn('id_siswa', $existingIds))
            ->get(['id_siswa']);

        if ($missing->isEmpty()) {
            return 0;
        }

        $guruId = $this->guruPencatatId();
        if (! $guruId) {
            return 0;
        }

        $now = now();
        $created = 0;
        foreach ($missing as $siswa) {
            try {
                $baru = Absen::whereDate('tanggal', $tanggal)
                    ->firstOrCreate(
                        ['id_siswa' => $siswa->id_siswa],
                        [
                            'id_guru' => $guruId,
                            'id_barcode' => null,
                            'metode' => 'manual_guru',
                            'status' => 'Alpa',
                            'keterangan' => 'Alpha otomatis - tanpa keterangan',
                            'waktu_absen' => $now,
                            'tanggal' => $tanggal,
                        ]
                    );

                if ($baru->wasRecentlyCreated) {
                    $created++;
                }
            } catch (QueryException $e) {
                // Abaikan hanya race-condition duplikat; error lain dilempar.
                if (! str_contains($e->getMessage(), 'Duplicate entry')) {
                    throw $e;
                }
            }
        }

        return $created;
    }
}

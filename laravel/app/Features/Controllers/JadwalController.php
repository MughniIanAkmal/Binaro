<?php

namespace App\Features\Controllers;

use App\Features\Models\AbsensiSetting;
use App\Features\Models\Guru;
use App\Features\Models\JadwalMataPelajaran as Jadwal;
use App\Features\Models\Kelas;
use App\Features\Models\MataPelajaran;
use App\Features\Models\TimeSlot;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Urutan hari untuk pengurutan di PHP (tidak bergantung FIELD() MySQL).
     */
    private const URUTAN_HARI = [
        'Senin' => 0, 'Selasa' => 1, 'Rabu' => 2, 'Kamis' => 3,
        'Jumat' => 4, 'Sabtu' => 5, 'Minggu' => 6,
    ];

    // Menampilkan semua jadwal
    public function index()
    {
        if (session('user_type') === 'guru') {
            return redirect()->route('guru.jadwal.index');
        }

        $jadwals = Jadwal::query()
            ->with(['mataPelajaran', 'guru', 'kelas', 'timeSlot'])
            ->get()
            ->sortBy(fn ($j) => [
                self::URUTAN_HARI[$j->hari] ?? 99,
                $j->timeSlot?->urutan ?? 99,
                $j->jam,
            ])
            ->values();

        // Grid mingguan: baris = slot waktu, kolom = hari.
        $slots = TimeSlot::orderBy('urutan')->get();
        $daftarHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        // Peta [hari][slot_id] => koleksi jadwal (bisa >1 bila beda kelas).
        $grid = [];
        foreach ($jadwals as $j) {
            if ($j->time_slot_id) {
                $grid[$j->hari][$j->time_slot_id][] = $j;
            }
        }

        return view('jadwal.index', compact('jadwals', 'slots', 'daftarHari', 'grid'));
    }

    // Menampilkan form tambah jadwal
    public function create()
    {
        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();
        $jamTutup = AbsensiSetting::get('batas_tutup', '12:00');
        $jamAwal = AbsensiSetting::get('batas_awal', '07:00');
        $slots = TimeSlot::orderBy('urutan')->get();

        return view('jadwal.create', compact('kelas', 'guru', 'mapel', 'jamTutup', 'jamAwal', 'slots'));
    }

    // Menampilkan detail jadwal
    public function show($id)
    {
        $jadwal = Jadwal::with(['mataPelajaran', 'guru', 'kelas', 'timeSlot'])->findOrFail($id);

        return view('jadwal.show', compact('jadwal'));
    }

    /**
     * Validasi bentrok berbasis slot waktu (lebih akurat daripada parsing teks).
     *
     * Aturan:
     *  - guru sama tidak boleh di hari+slot yang sama
     *  - kelas sama tidak boleh di hari+slot yang sama
     *  - ruang sama tidak boleh di hari+slot yang sama
     *  - slot istirahat tidak boleh dipakai mengajar
     *
     * @return string|null pesan error, atau null bila tidak ada bentrok
     */
    private function cekBentrokSlot(string $hari, TimeSlot $slot, int $idGuru, int $idKelas, ?string $ruang = null, ?int $excludeId = null): ?string
    {
        if ($slot->is_break) {
            return "Slot \"{$slot->nama}\" ({$slot->jam_mulai}-{$slot->jam_selesai}) adalah waktu istirahat dan tidak bisa dipakai untuk pelajaran.";
        }

        $base = Jadwal::with(['guru', 'kelas', 'mapel'])
            ->where('hari', $hari)
            ->where('time_slot_id', $slot->id_slot);

        if ($excludeId !== null) {
            $base->where('id_jadwal', '!=', $excludeId);
        }

        $existing = $base->get();

        foreach ($existing as $j) {
            if ((int) $j->id_guru === $idGuru) {
                $mapel = $j->mapel->nama_mapel ?? 'mata pelajaran';
                $kelas = $j->kelas->pararel ?? '-';

                return "Bentrok guru — {$j->guru->nama_guru} sudah mengajar {$mapel} di {$kelas} pada hari {$hari}, {$slot->nama} ({$slot->jam_mulai}-{$slot->jam_selesai}).";
            }
        }

        foreach ($existing as $j) {
            if ((int) $j->id_rooms === $idKelas) {
                $mapel = $j->mapel->nama_mapel ?? 'mata pelajaran';
                $guru = $j->guru->nama_guru ?? 'guru lain';

                return "Bentrok kelas — {$j->kelas->pararel} sudah punya {$mapel} ({$guru}) pada hari {$hari}, {$slot->nama} ({$slot->jam_mulai}-{$slot->jam_selesai}).";
            }
        }

        if ($ruang) {
            foreach ($existing as $j) {
                if ($j->ruang && strcasecmp((string) $j->ruang, $ruang) === 0) {
                    return "Bentrok ruang — {$ruang} sudah dipakai {$j->kelas->pararel} pada hari {$hari}, {$slot->nama} ({$slot->jam_mulai}-{$slot->jam_selesai}).";
                }
            }
        }

        return null;
    }

    /**
     * Aturan validasi bersama untuk store & update.
     */
    private function aturanValidasi(): array
    {
        return [
            'hari' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'time_slot_id' => ['required', 'exists:time_slots,id_slot'],
            'id_mapel' => ['required', 'exists:mata_pelajaran,id_mapel'],
            'id_guru' => ['required', 'exists:guru,id_guru'],
            'id_kelas' => ['required', 'exists:kelas,id_rooms'],
            'ruang' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function pesanValidasi(): array
    {
        return [
            'hari.in' => 'Pilihan hari tidak valid!',
            'time_slot_id.required' => 'Slot jam pelajaran wajib dipilih!',
            'time_slot_id.exists' => 'Slot jam yang dipilih tidak valid!',
            'id_mapel.exists' => 'Mata pelajaran yang dipilih tidak valid!',
            'id_guru.exists' => 'Guru yang dipilih tidak valid!',
            'id_kelas.required' => 'Kelas wajib dipilih!',
            'id_kelas.exists' => 'Kelas yang dipilih tidak valid!',
        ];
    }

    // Menyimpan jadwal baru
    public function store(Request $request)
    {
        $request->validate($this->aturanValidasi(), $this->pesanValidasi());

        $slot = TimeSlot::findOrFail($request->time_slot_id);

        $bentrok = $this->cekBentrokSlot(
            $request->hari,
            $slot,
            (int) $request->id_guru,
            (int) $request->id_kelas,
            $request->ruang ?: null
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['time_slot_id' => $bentrok]);
        }

        Jadwal::create([
            'hari' => $request->hari,
            'jam' => $slot->jam_teks,       // tetap diisi untuk kompatibilitas view guru/siswa
            'time_slot_id' => $slot->id_slot,
            'ruang' => $request->ruang ?: null,
            'id_mapel' => $request->id_mapel,
            'id_guru' => $request->id_guru,
            'id_rooms' => $request->id_kelas,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    // Menampilkan form edit
    public function edit($id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $kelas = Kelas::all();
        $guru = Guru::all();
        $mapel = MataPelajaran::all();
        $jamTutup = AbsensiSetting::get('batas_tutup', '12:00');
        $jamAwal = AbsensiSetting::get('batas_awal', '07:00');
        $slots = TimeSlot::orderBy('urutan')->get();

        return view('jadwal.edit', compact('jadwal', 'kelas', 'guru', 'mapel', 'jamTutup', 'jamAwal', 'slots'));
    }

    // Mengupdate jadwal
    public function update(Request $request, $id)
    {
        $jadwal = Jadwal::findOrFail($id);

        $request->validate($this->aturanValidasi(), $this->pesanValidasi());

        $slot = TimeSlot::findOrFail($request->time_slot_id);

        $bentrok = $this->cekBentrokSlot(
            $request->hari,
            $slot,
            (int) $request->id_guru,
            (int) $request->id_kelas,
            $request->ruang ?: null,
            (int) $jadwal->id_jadwal
        );

        if ($bentrok) {
            return back()->withInput()->withErrors(['time_slot_id' => $bentrok]);
        }

        $jadwal->update([
            'hari' => $request->hari,
            'jam' => $slot->jam_teks,
            'time_slot_id' => $slot->id_slot,
            'ruang' => $request->ruang ?: null,
            'id_mapel' => $request->id_mapel,
            'id_guru' => $request->id_guru,
            'id_rooms' => $request->id_kelas,
        ]);

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    // Mengupdate jadwal
    // Menghapus jadwal
    public function destroy($id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        return redirect()
            ->route('jadwal.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}

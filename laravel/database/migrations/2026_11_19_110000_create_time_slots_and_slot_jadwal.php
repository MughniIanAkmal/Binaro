<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal berbasis slot waktu.
     *
     * Alasan: sebelumnya `jam` disimpan sebagai teks "07.00-08.00" sehingga
     * deteksi bentrok harus mem-parse string di PHP (lambat & rawan salah).
     * Dengan `time_slot_id`, bentrok bisa diuji langsung dengan `=`.
     *
     * Kompatibilitas: kolom `jam` TETAP diisi (diturunkan dari slot) supaya
     * view guru & siswa yang sudah membaca `$item->jam` tidak perlu diubah.
     */
    public function up(): void
    {
        if (! Schema::hasTable('time_slots')) {
            Schema::create('time_slots', function (Blueprint $table) {
                $table->id('id_slot');
                $table->string('nama', 50);              // "Jam ke-1", "Istirahat"
                $table->string('jam_mulai', 5);          // "07:00"
                $table->string('jam_selesai', 5);        // "08:00"
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->boolean('is_break')->default(false);
                $table->timestamps();

                $table->unique(['jam_mulai', 'jam_selesai']);
            });

            // Seed slot default mengikuti jam sekolah (07:00–12:00).
            $slots = [
                ['Jam ke-1', '07:00', '08:00', 1, false],
                ['Jam ke-2', '08:00', '09:00', 2, false],
                ['Istirahat', '09:00', '09:15', 3, true],
                ['Jam ke-3', '09:15', '10:15', 4, false],
                ['Jam ke-4', '10:15', '11:15', 5, false],
                ['Jam ke-5', '11:15', '12:00', 6, false],
            ];

            foreach ($slots as [$nama, $mulai, $selesai, $urutan, $isBreak]) {
                DB::table('time_slots')->insert([
                    'nama' => $nama,
                    'jam_mulai' => $mulai,
                    'jam_selesai' => $selesai,
                    'urutan' => $urutan,
                    'is_break' => $isBreak,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('jadwal_mata_pelajaran', function (Blueprint $table) {
            if (! Schema::hasColumn('jadwal_mata_pelajaran', 'time_slot_id')) {
                $table->unsignedBigInteger('time_slot_id')->nullable()->after('jam');
            }
            if (! Schema::hasColumn('jadwal_mata_pelajaran', 'ruang')) {
                $table->string('ruang', 50)->nullable()->after('id_rooms');
            }
        });

        // Petakan jadwal lama (yang hanya punya teks `jam`) ke slot yang cocok.
        $this->backfillTimeSlots();

        // Guard bentrok di level database. Nullable: jadwal lama tanpa slot tetap valid.
        Schema::table('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->unique(['id_guru', 'hari', 'time_slot_id'], 'uq_jadwal_guru_slot');
            $table->unique(['id_rooms', 'hari', 'time_slot_id'], 'uq_jadwal_kelas_slot');
        });
    }

    private function backfillTimeSlots(): void
    {
        $slots = DB::table('time_slots')->get();

        foreach (DB::table('jadwal_mata_pelajaran')->whereNull('time_slot_id')->get() as $jadwal) {
            $rentang = $this->parseRentang((string) $jadwal->jam);
            if ($rentang === null) {
                continue;
            }
            [$mulai, $selesai] = $rentang;

            $match = $slots->first(fn ($s) => $this->toMenit($s->jam_mulai) === $mulai
                && $this->toMenit($s->jam_selesai) === $selesai);

            if ($match) {
                DB::table('jadwal_mata_pelajaran')
                    ->where('id_jadwal', $jadwal->id_jadwal)
                    ->update(['time_slot_id' => $match->id_slot]);
            }
        }
    }

    private function parseRentang(string $jam): ?array
    {
        $clean = preg_replace('/\s+/', '', str_replace(':', '.', $jam));
        $parts = explode('-', $clean);
        if (count($parts) !== 2) {
            return null;
        }

        $mulai = $this->toMenit($parts[0]);
        $selesai = $this->toMenit($parts[1]);

        if ($mulai === null || $selesai === null || $selesai <= $mulai) {
            return null;
        }

        return [$mulai, $selesai];
    }

    private function toMenit(string $waktu): ?int
    {
        $waktu = str_replace(['.', ':'], ':', trim($waktu));
        $parts = explode(':', $waktu);
        if (count($parts) !== 2 || ! ctype_digit($parts[0]) || ! ctype_digit($parts[1])) {
            return null;
        }

        return ((int) $parts[0]) * 60 + (int) $parts[1];
    }

    public function down(): void
    {
        Schema::table('jadwal_mata_pelajaran', function (Blueprint $table) {
            $table->dropUnique('uq_jadwal_guru_slot');
            $table->dropUnique('uq_jadwal_kelas_slot');

            $cols = array_filter(
                ['time_slot_id', 'ruang'],
                fn ($c) => Schema::hasColumn('jadwal_mata_pelajaran', $c)
            );

            if (! empty($cols)) {
                $table->dropColumn($cols);
            }
        });

        Schema::dropIfExists('time_slots');
    }
};

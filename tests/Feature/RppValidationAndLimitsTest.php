<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RppValidationAndLimitsTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $mapel;
    protected $kelas;
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nip' => '198801012015011001',
            'nama_guru' => 'Siti Aminah, M.Pd',
            'email' => 'siti@binaro.sch.id',
            'password' => 'password',
        ]);

        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Bahasa Indonesia',
            'deskripsi' => 'Mapel Bahasa Indonesia',
        ]);

        $this->kelas = Kelas::create([
            'pararel' => 'Kelas 4-A',
            'tingkat' => 4,
        ]);

        $this->session = [
            'user_id' => $this->guru->id_guru,
            'user_type' => 'guru',
            'user_name' => $this->guru->nama_guru,
        ];
    }

    public function test_judul_rpp_rejects_symbols_and_exceeding_100_chars()
    {
        // 1. Simbol pada judul RPP
        $resSymbol = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul 1: Pecahan & Operasi Hitung (Part 1)!',
            'action' => 'draft',
        ]);
        $resSymbol->assertSessionHasErrors(['judul_rpp']);

        // 2. Melebihi 100 karakter
        $resLong = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => str_repeat('A', 101),
            'action' => 'draft',
        ]);
        $resLong->assertSessionHasErrors(['judul_rpp']);

        // 3. Judul valid (hanya huruf, angka, spasi <= 100 karakter)
        $resValid = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul 1 Pecahan dan Operasi Hitung Bilangan 100',
            'action' => 'draft',
        ]);
        $resValid->assertSessionHas('success');
    }

    public function test_modul_ke_rejects_symbols_and_exceeding_20_chars()
    {
        // 1. Simbol pada modul_ke
        $resSymbol = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Tanpa Simbol',
            'modul_ke' => 'Modul #28/A',
            'action' => 'draft',
        ]);
        $resSymbol->assertSessionHasErrors(['modul_ke']);

        // 2. Melebihi 20 karakter
        $resLong = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Tanpa Simbol',
            'modul_ke' => 'Modul Ke 28 Sangat Panjang Sekali',
            'action' => 'draft',
        ]);
        $resLong->assertSessionHasErrors(['modul_ke']);

        // 3. Valid modul_ke
        $resValid = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Tanpa Simbol Modul',
            'modul_ke' => 'Modul 28',
            'action' => 'draft',
        ]);
        $resValid->assertSessionHas('success');
    }

    public function test_alokasi_waktu_limit_4_chars()
    {
        // 1. Melebihi 4 karakter (misal: "2 JP (90m)") -> ditolak
        $resLong = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Alokasi Waktu',
            'alokasi_waktu' => '2 JP (70m)',
            'action' => 'draft',
        ]);
        $resLong->assertSessionHasErrors(['alokasi_waktu']);

        // 2. 4 karakter atau kurang (misal: "2 JP", "4 JP") -> diterima
        $resValid = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Alokasi Waktu 2',
            'alokasi_waktu' => '2 JP',
            'action' => 'draft',
        ]);
        $resValid->assertSessionHas('success');
    }

    public function test_target_jadwal_ruang_deskripsi_limits()
    {
        // 1. Target roster jadwal > 50 karakter -> ditolak
        $resJadwal = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Target Jadwal',
            'target_jadwal' => str_repeat('J', 51),
            'action' => 'draft',
        ]);
        $resJadwal->assertSessionHasErrors(['target_jadwal']);

        // 2. Ruang lokasi > 50 karakter -> ditolak
        $resRuang = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Ruang Lokasi',
            'ruang' => str_repeat('R', 51),
            'action' => 'draft',
        ]);
        $resRuang->assertSessionHasErrors(['ruang']);

        // 3. Deskripsi > 500 karakter -> ditolak
        $resDesk = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Deskripsi',
            'deskripsi' => str_repeat('D', 501),
            'action' => 'draft',
        ]);
        $resDesk->assertSessionHasErrors(['deskripsi']);
    }

    public function test_label_tags_rejects_symbols()
    {
        // 1. Tag mengandung simbol (misal @tag, #tema, <script>, dsb)
        $resSymbolTag = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Label Tag',
            'custom_tags' => 'Pecahan, Video#1, Animasi@Interaktif',
            'action' => 'draft',
        ]);
        $resSymbolTag->assertSessionHasErrors(['custom_tags']);

        // 2. Tag sah (hanya huruf, angka, spasi yang dipisah koma)
        $resValidTag = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Sah Label Tag Valid',
            'custom_tags' => 'Pecahan, Video Animasi 1, LKPD Proyektor',
            'action' => 'draft',
        ]);
        $resValidTag->assertSessionHas('success');

        $rpp = Rpp::where('judul_rpp', 'Judul Sah Label Tag Valid')->first();
        $this->assertNotNull($rpp);
        $this->assertContains('Video Animasi 1', $rpp->komponen_checklist['tags']);
    }

    public function test_update_rpp_enforces_same_symbol_and_length_validations()
    {
        $rpp = Rpp::create([
            'id_guru' => $this->guru->id_guru,
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Awal Sah',
            'status' => 'draft',
        ]);

        // Update dengan simbol di judul
        $resSymbol = $this->withSession($this->session)->put(route('guru.rpp.update', $rpp->id_rpp), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Update Mengandung Simbol!',
            'action' => 'draft',
        ]);
        $resSymbol->assertSessionHasErrors(['judul_rpp']);

        // Update dengan alokasi waktu > 4 karakter
        $resWaktu = $this->withSession($this->session)->put(route('guru.rpp.update', $rpp->id_rpp), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Update Sah',
            'alokasi_waktu' => '500 Menit',
            'action' => 'draft',
        ]);
        $resWaktu->assertSessionHasErrors(['alokasi_waktu']);

        // Update dengan tag bersimbol
        $resTag = $this->withSession($this->session)->put(route('guru.rpp.update', $rpp->id_rpp), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Update Sah',
            'custom_tags' => 'Tag Satu, Tag@Dua',
            'action' => 'draft',
        ]);
        $resTag->assertSessionHasErrors(['custom_tags']);

        // Update sah
        $resValid = $this->withSession($this->session)->put(route('guru.rpp.update', $rpp->id_rpp), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Judul Update Berhasil Sah',
            'modul_ke' => 'Modul 28',
            'alokasi_waktu' => '2 JP',
            'target_jadwal' => 'Senin 08.00 - 09.30 WIB',
            'ruang' => 'Ruang 4B',
            'deskripsi' => 'Deskripsi sah dalam batas',
            'custom_tags' => 'Modul Terpadu, Evaluasi Harian',
            'action' => 'draft',
        ]);
        $resValid->assertSessionHas('success');
    }

    public function test_rpp_file_only_accepts_pdf_and_word_documents()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        // 1. Rejected: PNG image
        $pngFile = \Illuminate\Http\UploadedFile::fake()->create('skenario.png', 500, 'image/png');
        $resPng = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul Ajar IPA',
            'file_rpp' => $pngFile,
            'action' => 'draft',
        ]);
        $resPng->assertSessionHasErrors(['file_rpp']);

        // 2. Rejected: Executable or arbitrary file
        $exeFile = \Illuminate\Http\UploadedFile::fake()->create('program.exe', 500);
        $resExe = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul Ajar IPA',
            'file_rpp' => $exeFile,
            'action' => 'draft',
        ]);
        $resExe->assertSessionHasErrors(['file_rpp']);

        // 3. Accepted: PDF
        $pdfFile = \Illuminate\Http\UploadedFile::fake()->create('modul_ajar.pdf', 500, 'application/pdf');
        $resPdf = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul Ajar IPA PDF',
            'file_rpp' => $pdfFile,
            'action' => 'draft',
        ]);
        $resPdf->assertSessionHas('success');

        // 4. Accepted: Word DOCX
        $docxFile = \Illuminate\Http\UploadedFile::fake()->create('modul_ajar.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $resDocx = $this->withSession($this->session)->post(route('guru.rpp.store'), [
            'id_mapel' => $this->mapel->id_mapel,
            'judul_rpp' => 'Modul Ajar IPA Word',
            'file_rpp' => $docxFile,
            'action' => 'draft',
        ]);
        $resDocx->assertSessionHas('success');
    }
}

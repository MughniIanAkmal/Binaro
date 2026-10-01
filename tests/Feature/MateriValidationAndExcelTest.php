<?php

namespace Tests\Feature;

use App\Models\Bab;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Materi;
use App\Models\Quiz;
use App\Models\SubBab;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MateriValidationAndExcelTest extends TestCase
{
    use RefreshDatabase;

    protected $guru;
    protected $mapel;
    protected $bab;
    protected $subBab;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guru = Guru::create([
            'nama_guru' => 'Guru Pengampu',
            'nip'       => '198501012010011010',
            'password'  => 'password123',
        ]);

        $this->mapel = MataPelajaran::create([
            'nama_mapel' => 'Bahasa & Sains Terpadu',
        ]);

        $this->bab = Bab::create([
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => 'Bab 1 - Pengenalan Sains',
        ]);

        $this->subBab = SubBab::create([
            'id_bab'       => $this->bab->id_bab,
            'nama_sub_bab' => 'Sub-Bab 1.1: Ekosistem',
        ]);
    }

    public function test_nama_bab_validation_max_characters_and_symbols()
    {
        $session = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru'];

        // 1. Melebihi 100 karakter -> harus ditolak
        $longName = str_repeat('A', 101);
        $resLong = $this->withSession($session)->post(route('guru.materi.store.bab'), [
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => $longName,
        ]);
        $resLong->assertSessionHasErrors(['nama_bab']);

        // 2. Mengandung simbol fatal (misal tanda baca, simbol, tanda kurung, dsb) -> harus ditolak
        $symbolName = 'Bab 2 - Fisika & Kimia: Pengenalan Unsur (Part 1)';
        $resSymbol = $this->withSession($session)->post(route('guru.materi.store.bab'), [
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => $symbolName,
        ]);
        $resSymbol->assertSessionHasErrors(['nama_bab']);

        // 3. Nama Bab yang sah (hanya huruf, angka, dan spasi) -> berhasil
        $validName = "Bab 2 Fisika dan Kimia Pengenalan Unsur Bagian 1";
        $resValid = $this->withSession($session)->post(route('guru.materi.store.bab'), [
            'id_mapel' => $this->mapel->id_mapel,
            'nama_bab' => $validName,
        ]);
        $resValid->assertSessionHas('success');
        $this->assertDatabaseHas('bab', ['nama_bab' => $validName]);
    }

    public function test_nama_sub_bab_validation_max_characters_and_symbols()
    {
        $session = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru'];

        // 1. Melebihi 100 karakter -> harus ditolak
        $resLong = $this->withSession($session)->post(route('guru.materi.store.sub-bab'), [
            'id_bab'       => $this->bab->id_bab,
            'nama_sub_bab' => str_repeat('B', 101),
        ]);
        $resLong->assertSessionHasErrors(['nama_sub_bab']);

        // 2. Mengandung simbol -> harus ditolak
        $resSymbol = $this->withSession($session)->post(route('guru.materi.store.sub-bab'), [
            'id_bab'       => $this->bab->id_bab,
            'nama_sub_bab' => 'Sub-Bab 1.2: Rantai Makanan & Jaring (A-1)',
        ]);
        $resSymbol->assertSessionHasErrors(['nama_sub_bab']);

        // 3. Nama Sub-Bab sah (hanya huruf, angka, dan spasi) -> berhasil
        $validSub = 'Sub Bab 12 Rantai Makanan dan Jaring Ekosistem';
        $resValid = $this->withSession($session)->post(route('guru.materi.store.sub-bab'), [
            'id_bab'       => $this->bab->id_bab,
            'nama_sub_bab' => $validSub,
        ]);
        $resValid->assertSessionHas('success');
        $this->assertDatabaseHas('sub_bab', ['nama_sub_bab' => $validSub]);
    }

    public function test_nama_materi_validation_and_gdrive_rejection()
    {
        $session = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru'];

        // 1. Judul materi melebihi 100 karakter -> ditolak
        $resLong = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => str_repeat('M', 105),
            'tipe_materi'  => 'video',
            'url_video'    => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $resLong->assertSessionHasErrors(['judul_materi']);

        // 2. Judul materi dengan simbol -> ditolak
        $resSymbol = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Materi Video: Bagian Sel & Fungsi (Part 1)',
            'tipe_materi'  => 'video',
            'url_video'    => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $resSymbol->assertSessionHasErrors(['judul_materi']);

        // 3. Link Video Google Drive -> HARUS DITOLAK
        $resGdrive = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Video dari Google Drive',
            'tipe_materi'  => 'video',
            'url_video'    => 'https://drive.google.com/file/d/1A2B3C4D5E/view?usp=sharing',
        ]);
        $resGdrive->assertSessionHasErrors(['url_video']);

        // 4. Link Video acak non YouTube / non MP4 -> HARUS DITOLAK
        $resRandom = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Video dari Facebook',
            'tipe_materi'  => 'video',
            'url_video'    => 'https://facebook.com/watch?v=12345',
        ]);
        $resRandom->assertSessionHasErrors(['url_video']);

        // 5. Link Video YouTube resmi dan judul bebas simbol -> DITERIMA
        $resYt = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Video Pembelajaran Bagian Sel Hewan',
            'tipe_materi'  => 'video',
            'url_video'    => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ]);
        $resYt->assertSessionHas('success');

        // 6. Link Video MP4 langsung dan judul bebas simbol -> DITERIMA
        $resMp4 = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Video Pembelajaran Animasi MP4',
            'tipe_materi'  => 'video',
            'url_video'    => 'https://cdn.sekolah.id/videos/materi_sel.mp4',
        ]);
        $resMp4->assertSessionHas('success');
    }

    public function test_kuis_import_only_accepts_excel_format()
    {
        $session = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru'];

        $quiz = Quiz::create([
            'id_sub_bab' => $this->subBab->id_sub_bab,
            'judul_quiz' => 'Kuis Harian Sains',
        ]);

        // 1. Upload file TXT -> HARUS DITOLAK (format non-spreadsheet)
        $fakeTxt = UploadedFile::fake()->create('soal.txt', 100, 'text/plain');
        $resTxt = $this->withSession($session)->post(route('guru.quiz.import', $quiz->id_quiz), [
            'file_excel' => $fakeTxt,
        ]);
        $resTxt->assertSessionHasErrors(['file_excel']);

        // 2. Upload file CSV valid -> DITERIMA & soal terimport
        $csvContent = "pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban\n" .
                      "Berapa hasil 5 + 5?,8,9,10,11,C\n" .
                      "Ibu kota Indonesia?,Jakarta,Surabaya,Nusantara,Medan,C\n";
        $fakeCsv = UploadedFile::fake()->createWithContent('soal.csv', $csvContent);
        $resCsv = $this->withSession($session)->post(route('guru.quiz.import', $quiz->id_quiz), [
            'file_excel' => $fakeCsv,
        ]);
        $resCsv->assertSessionHas('success');
        $this->assertDatabaseHas('soal_quiz', [
            'id_quiz' => $quiz->id_quiz,
            'pertanyaan' => 'Berapa hasil 5 + 5?',
            'kunci_jawaban' => 'C',
        ]);

        // 3. Download Template Excel -> Menghasilkan file .xlsx asli
        $resDownload = $this->withSession($session)->get(route('guru.quiz.template.download'));
        $resDownload->assertOk();
        $this->assertStringContainsString('spreadsheetml.sheet', $resDownload->headers->get('content-type'));
        $this->assertStringContainsString('template_soal_kuis.xlsx', $resDownload->headers->get('content-disposition'));
    }

    public function test_materi_pdf_and_kuis_titles_and_excel_rejection_in_materi_store()
    {
        $session = ['user_id' => $this->guru->id_guru, 'user_type' => 'guru'];

        // 1. Dokumen PDF: Judul materi bersimbol -> ditolak
        $fakePdf = UploadedFile::fake()->create('materi.pdf', 500, 'application/pdf');
        $resPdfSymbol = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Dokumen PDF: Materi #1 (Bab 1.1)',
            'tipe_materi'  => 'dokumen',
            'file_pdf'     => $fakePdf,
        ]);
        $resPdfSymbol->assertSessionHasErrors(['judul_materi']);

        // 2. Dokumen PDF: File bukan PDF -> ditolak
        $fakeDoc = UploadedFile::fake()->create('materi.docx', 500, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $resNotPdf = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Dokumen Pembelajaran IPA Bab Satu',
            'tipe_materi'  => 'dokumen',
            'file_pdf'     => $fakeDoc,
        ]);
        $resNotPdf->assertSessionHasErrors(['file_pdf']);

        // 3. Dokumen PDF: Sah -> berhasil disimpan
        $resPdfValid = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Dokumen Pembelajaran IPA Bab Satu',
            'tipe_materi'  => 'dokumen',
            'file_pdf'     => $fakePdf,
        ]);
        $resPdfValid->assertSessionHas('success');

        // 4. Kuis: Judul bersimbol -> ditolak
        $resKuisSymbol = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi @ Bab 1: 100% Benar!',
            'tipe_materi'  => 'kuis',
        ]);
        $resKuisSymbol->assertSessionHasErrors(['judul_materi']);

        // 5. Kuis: File format bukan Excel/CSV (misal DOCX atau TXT) -> ditolak
        $fakeTxt = UploadedFile::fake()->create('soal.docx', 100, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $resKuisNonSpreadsheet = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi Pemahaman Ekosistem',
            'tipe_materi'  => 'kuis',
            'file_excel'   => $fakeTxt,
        ]);
        $resKuisNonSpreadsheet->assertSessionHasErrors(['file_excel']);

        // 6. Kuis: File CSV -> diterima dan kuis dibuat
        $csvContent = "pertanyaan,opsi_a,opsi_b,opsi_c,opsi_d,kunci_jawaban\n" .
                      "Berapa 10 x 10?,50,100,150,200,B\n";
        $fakeCsv = UploadedFile::fake()->createWithContent('soal.csv', $csvContent);
        $resKuisCsvValid = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi Pemahaman Ekosistem CSV',
            'tipe_materi'  => 'kuis',
            'file_excel'   => $fakeCsv,
        ]);
        $resKuisCsvValid->assertSessionHas('success');

        // 7. Kuis: File Excel .xlsx -> diterima dan kuis dibuat
        $fakeXlsx = UploadedFile::fake()->create('soal.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $resKuisValid = $this->withSession($session)->post(route('guru.materi.store'), [
            'id_sub_bab'   => $this->subBab->id_sub_bab,
            'judul_materi' => 'Kuis Evaluasi Pemahaman Ekosistem',
            'tipe_materi'  => 'kuis',
            'file_excel'   => $fakeXlsx,
        ]);
        $resKuisValid->assertSessionHas('success');
    }

    public function test_youtube_embed_attributes_and_url_variations(): void
    {
        $m1 = new Materi(['url_video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $this->assertEquals('dQw4w9WgXcQ', $m1->youtube_id);
        $this->assertStringContainsString('https://www.youtube.com/embed/dQw4w9WgXcQ', $m1->youtube_embed_url);
        $this->assertEquals('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $m1->youtube_watch_url);

        // youtu.be with playlist query params
        $m2 = new Materi(['url_video' => 'https://youtu.be/AR__qzglDdc?list=RDAR__qzglDdc']);
        $this->assertEquals('AR__qzglDdc', $m2->youtube_id);
        $this->assertStringContainsString('https://www.youtube.com/embed/AR__qzglDdc', $m2->youtube_embed_url);
        $this->assertEquals('https://www.youtube.com/watch?v=AR__qzglDdc', $m2->youtube_watch_url);

        // shorts
        $m3 = new Materi(['url_video' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ?feature=share']);
        $this->assertEquals('dQw4w9WgXcQ', $m3->youtube_id);

        // mp4 has no youtube_id
        $m4 = new Materi(['url_video' => 'https://cdn.sekolah.id/videos/materi.mp4']);
        $this->assertNull($m4->youtube_id);
        $this->assertNull($m4->youtube_embed_url);
        $this->assertEquals('https://cdn.sekolah.id/videos/materi.mp4', $m4->youtube_watch_url);
    }
}


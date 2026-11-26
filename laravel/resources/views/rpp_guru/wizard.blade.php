@extends('layouts.guru')

@section('page_title', $isNew ? 'Buat Modul Ajar' : 'Editor Modul Ajar')
@section('page_subtitle', 'Susun rencana pembelajaran bertahap dan simpan sebagai draf kapan saja.')

@section('header_actions')
    <a href="{{ route('guru.rpp.index') }}" class="inline-flex items-center gap-2 px-4 h-10 rounded-xl text-xs font-bold text-ink-soft bg-white border border-line hover:bg-surface-alt transition">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Kembali ke RPP
    </a>
@endsection

@section('content')
@php
    $steps = ['Informasi Umum', 'CP & TP', 'Pertanyaan Pemantik', 'Langkah Pembelajaran', 'Media', 'Asesmen', 'Diferensiasi', 'Review & Ajukan'];
    $content = old('konten', $rppSession->konten ?? []);
    $content = is_array($content) ? $content : [];
    $dims = ['Beriman dan berakhlak mulia', 'Berkebinekaan global', 'Bergotong royong', 'Mandiri', 'Bernalar kritis', 'Kreatif'];
    $tpList = $content['tp_list'] ?? [['teks' => '', 'dimensi' => []], ['', '']];
    if (!is_array($tpList) || count($tpList) < 3) $tpList = array_pad($tpList, 3, ['teks' => '', 'dimensi' => []]);
    $meetingCount = max(1, (int) ($rpp->total_pertemuan ?: 1));
@endphp

<div class="max-w-6xl mx-auto space-y-5" id="rpp-wizard" data-rpp-id="{{ $rpp->id_rpp ?? '' }}" data-step="{{ $step }}" data-meeting="{{ $nomorPertemuan }}">
    @if($errors->any())
        <div class="rounded-xl border border-danger/20 bg-danger-tint text-danger p-4 text-sm" role="alert">
            <strong class="block mb-1">Masih ada bagian yang perlu diperbaiki</strong>
            <ul class="list-disc pl-5 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <section class="card p-5 sm:p-6 space-y-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-extrabold tracking-[0.08em] uppercase text-primary">{{ $rpp->nomor_rpp ?? 'DRAF MODUL AJAR' }}</p>
                <h2 class="text-xl font-bold text-ink mt-1">{{ $rpp->judul_rpp ?: 'Rencana pembelajaran Anda' }}</h2>
                <p class="text-xs text-ink-soft mt-1">Lengkapi setiap bagian. Progres tersimpan otomatis sebagai draf.</p>
            </div>
            <div class="flex items-center gap-2 text-xs text-ink-soft" aria-live="polite">
                <span id="save-indicator" class="w-2 h-2 rounded-full bg-ink-muted"></span>
                <span id="save-status">Perubahan belum disimpan</span>
            </div>
        </div>

        <nav aria-label="Langkah Modul Ajar" class="overflow-x-auto pb-1">
            <ol class="flex min-w-[760px] items-center gap-2">
                @foreach($steps as $index => $label)
                    @php $n = $index + 1; @endphp
                    <li class="flex-1">
                        <a href="{{ route('guru.rpp.wizard.edit', [$rpp->id_rpp ?? 0, 'step' => $n, 'pertemuan' => $nomorPertemuan]) }}"
                           data-wizard-step-link="{{ $n }}"
                           class="flex items-center gap-2 rounded-xl border px-3 py-2.5 text-[11px] font-semibold transition {{ $step === $n ? 'bg-primary text-white border-primary' : ($step > $n ? 'bg-primary-tint text-primary border-primary/20' : 'bg-white text-ink-soft border-line') }}">
                            <span class="inline-flex w-6 h-6 shrink-0 items-center justify-center rounded-full {{ $step === $n ? 'bg-white/20' : 'bg-surface-alt' }}">{{ $n }}</span>
                            <span class="whitespace-nowrap">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>
        <div class="h-1.5 bg-surface-alt rounded-full overflow-hidden"><div class="h-full bg-primary rounded-full transition-all" style="width: {{ (int) ($step / 8 * 100) }}%"></div></div>
    </section>

    <form id="wizard-form" class="space-y-5" autocomplete="off">
        @csrf
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="pertemuan" value="{{ $nomorPertemuan }}">
        <input type="hidden" name="konten[cp_id]" id="cp-id" value="{{ $content['cp_id'] ?? '' }}">

        @if($step === 1)
            <section class="card p-5 sm:p-6 space-y-5">
                <div><h3 class="text-lg font-bold">Informasi umum</h3><p class="text-xs text-ink-soft mt-1">Data sekolah dan identitas guru diambil dari akun Binaro.</p></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="block text-xs font-semibold mb-1.5">Nama Sekolah</label><input value="{{ config('binaro.nama_sekolah') }}" readonly class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div><label class="block text-xs font-semibold mb-1.5">Nama Guru</label><input value="{{ $guru->nama_guru }} (NIP {{ $guru->nip }})" readonly class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div><label for="id_mapel" class="block text-xs font-semibold mb-1.5">Mata Pelajaran <span class="text-danger">*</span></label><select id="id_mapel" name="id_mapel" required class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih mata pelajaran</option>@foreach($mapelList as $mapel)<option value="{{ $mapel->id_mapel }}" @selected((string) $rpp->id_mapel === (string) $mapel->id_mapel)>{{ $mapel->nama_mapel }}</option>@endforeach</select></div>
                    <div><label for="id_rooms" class="block text-xs font-semibold mb-1.5">Kelas <span class="text-danger">*</span></label><select id="id_rooms" name="id_rooms" required class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih kelas</option>@foreach($kelasList as $kelas)@php $kodeFase = $kelas->fase ? str_replace('Fase ', '', $kelas->fase) : ''; @endphp<option value="{{ $kelas->id_rooms }}" data-fase="{{ $kodeFase }}" @selected((string) $rpp->id_rooms === (string) $kelas->id_rooms)>{{ $kelas->pararel }}</option>@endforeach</select><p id="fase-preview" class="text-[11px] text-ink-soft mt-1">Fase ditentukan otomatis dari kelas (A: kelas I–II, B: III–IV, C: V–VI).</p></div>
                    <div><label for="id_bab" class="block text-xs font-semibold mb-1.5">Bab / Unit Pembelajaran</label><select id="id_bab" name="id_bab" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih bab (opsional)</option>@foreach($babList ?? [] as $bab)<option value="{{ $bab->id_bab }}" data-mapel="{{ $bab->id_mapel }}" @selected((string) $rpp->id_bab === (string) $bab->id_bab)>{{ $bab->mataPelajaran->nama_mapel ?? '' }} — {{ $bab->nama_bab }}</option>@endforeach</select></div>
                    <div><label for="semester" class="block text-xs font-semibold mb-1.5">Semester</label><select id="semester" name="semester" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="ganjil" @selected(($rpp->semester ?? config('binaro.semester')) === 'ganjil')>Ganjil</option><option value="genap" @selected(($rpp->semester ?? config('binaro.semester')) === 'genap')>Genap</option></select></div>
                    <div><label for="tahun_ajaran" class="block text-xs font-semibold mb-1.5">Tahun Ajaran</label><input id="tahun_ajaran" name="tahun_ajaran" value="{{ $rpp->tahun_ajaran ?? config('binaro.tahun_ajaran') }}" pattern="\d{4}/\d{4}" placeholder="2026/2027" required class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div class="md:col-span-2"><label for="judul_rpp" class="block text-xs font-semibold mb-1.5">Judul Tema / Topik <span class="text-danger">*</span></label><input id="judul_rpp" name="judul_rpp" value="{{ $rpp->judul_rpp }}" required maxlength="150" placeholder="Contoh: Bangun Datar di Sekitar Kita" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div><label for="alokasi_jp" class="block text-xs font-semibold mb-1.5">Alokasi per Pertemuan</label><div class="flex items-center gap-2"><input id="alokasi_jp" name="alokasi_jp" type="number" min="1" max="20" value="{{ max(1, (int) (($rpp->alokasi_waktu_menit ?: 70) / 35)) }}" class="w-28 px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><span class="text-xs text-ink-soft">JP (1 JP = 35 menit)</span></div></div>
                    <div><label for="total_pertemuan" class="block text-xs font-semibold mb-1.5">Jumlah Pertemuan</label><input id="total_pertemuan" name="total_pertemuan" type="number" min="1" max="20" value="{{ $meetingCount }}" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><p class="text-[11px] text-ink-soft mt-1">Jumlah pertemuan tidak dapat dikurangi setelah draf dibuat.</p></div>
                    <div><label for="target_jadwal" class="block text-xs font-semibold mb-1.5">Target Jadwal (opsional)</label><input id="target_jadwal" name="target_jadwal" value="{{ $rpp->target_jadwal }}" placeholder="Senin, 07.00 - 08.10 WIB" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div><label for="ruang" class="block text-xs font-semibold mb-1.5">Ruang (opsional)</label><input id="ruang" name="ruang" value="{{ $rpp->ruang }}" placeholder="Ruang kelas" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div>
                    <div class="md:col-span-2"><label for="kesiapan_peserta_didik" class="block text-xs font-semibold mb-1.5">Identifikasi Kesiapan Peserta Didik (Asesmen Awal)</label><textarea id="kesiapan_peserta_didik" name="konten[kesiapan_peserta_didik]" rows="3" placeholder="Contoh: Sebagian besar murid kelas sudah mengenal bilangan cacah 1–100, 4 murid masih memerlukan pendampingan konkret..." class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ $content['kesiapan_peserta_didik'] ?? '' }}</textarea><p class="text-[11px] text-ink-soft mt-1">Catatan singkat kondisi awal, minat, atau kebutuhan belajar murid di kelas.</p></div>
                </div>
                @if(isset($rpp->id_rpp))<div class="flex items-center gap-2 border-t border-line pt-4"><label for="nomor-pertemuan" class="text-xs font-semibold">Mengisi pertemuan</label><select id="nomor-pertemuan" class="px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm">@for($i=1;$i<=$meetingCount;$i++)<option value="{{ $i }}" @selected($i === $nomorPertemuan)>Pertemuan {{ $i }}</option>@endfor</select></div>@endif
            </section>
        @elseif($step === 2)
            <section class="card p-5 sm:p-6 space-y-5">
                <div><h3 class="text-lg font-bold">Capaian & tujuan pembelajaran</h3><p class="text-xs text-ink-soft mt-1">Pertemuan {{ $nomorPertemuan }} dari {{ $meetingCount }}.</p></div>
                <div><label class="block text-xs font-semibold mb-1.5" for="cp-select">Capaian Pembelajaran (CP) <span class="text-danger">*</span></label><select id="cp-select" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih CP sesuai mapel dan fase</option>@foreach($cpList as $cp)<option value="{{ $cp->id_cp }}" data-text="{{ $cp->cp_text }}" data-elemen="{{ $cp->elemen }}" @selected(($content['cp_id'] ?? null) == $cp->id_cp)>{{ $cp->elemen ? $cp->elemen.' — ' : '' }}{{ \Illuminate\Support\Str::limit($cp->cp_text, 100) }}</option>@endforeach</select><div id="cp-text" class="mt-3 p-4 rounded-xl bg-primary-tint border border-primary/10 text-sm text-ink leading-relaxed">{{ $content['cp_text'] ?? 'Pilih mata pelajaran dan kelas pada langkah pertama, lalu pilih CP yang tersedia.' }}</div>@if($cpList->isEmpty())<p class="mt-2 text-xs text-warning">Belum tersedia CP aktif untuk mapel dan fase ini. Hubungi Admin agar mengisi Master CP sebelum pengajuan.</p>@endif</div>
                <div><label for="pemahaman_bermakna" class="block text-xs font-semibold mb-1.5">Pemahaman Bermakna (Meaningful Learning)</label><textarea id="pemahaman_bermakna" name="konten[pemahaman_bermakna]" rows="2" placeholder="Contoh: Dengan memahami bentuk bangun datar, murid dapat mengenali benda-benda di lingkungan sekolah dan rumah." class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ $content['pemahaman_bermakna'] ?? '' }}</textarea></div>
                <div class="space-y-4"><div><h4 class="text-sm font-bold">Tujuan Pembelajaran (minimal 3)</h4><p class="text-xs text-ink-soft">Setiap TP dihubungkan dengan minimal satu dimensi Profil Pelajar Pancasila.</p></div>
                    @for($i=0;$i<max(3,count($tpList));$i++)
                        @php $tp = $tpList[$i] ?? []; $tp = is_array($tp) ? $tp : ['teks' => $tp, 'dimensi' => []]; @endphp
                        <fieldset class="rounded-xl border border-line p-4 space-y-3"><legend class="px-1 text-xs font-bold text-primary">Tujuan {{ $i + 1 }}</legend><textarea name="konten[tp_list][{{ $i }}][teks]" rows="2" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm" placeholder="Murid mampu...">{{ $tp['teks'] ?? '' }}</textarea><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">@foreach($dims as $dim)<label class="flex items-center gap-2 rounded-lg bg-surface-alt px-3 py-2 text-xs"><input type="checkbox" name="konten[tp_list][{{ $i }}][dimensi][]" value="{{ $dim }}" @checked(in_array($dim, $tp['dimensi'] ?? [], true)) class="rounded border-line text-primary focus:ring-primary"><span>{{ $dim }}</span></label>@endforeach</div></fieldset>
                    @endfor
                    <button type="button" id="add-tp" class="px-3 py-2 rounded-xl border border-line bg-white text-xs font-semibold text-primary hover:bg-primary-tint"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah tujuan</button>
                </div>
            </section>
        @elseif($step === 3)
            <section class="card p-5 sm:p-6 space-y-4"><div><h3 class="text-lg font-bold">Pertanyaan pemantik</h3><p class="text-xs text-ink-soft mt-1">Tuliskan pertanyaan terbuka yang membangun rasa ingin tahu murid.</p></div><label for="pemantik" class="text-xs font-semibold">Pertanyaan (satu per baris) <span class="text-danger">*</span></label><textarea id="pemantik" name="konten[pertanyaan_pemantik_text]" rows="7" placeholder="Apa yang membuat bentuk-bentuk ini berbeda?&#10;Di mana kita menemukan bangun datar di sekitar kita?" class="w-full px-3 py-3 rounded-xl bg-surface-alt border border-line text-sm">{{ implode("\n", $content['pertanyaan_pemantik'] ?? []) }}</textarea></section>
        @elseif($step === 4)
            @php $alokasiMenit = (int) ($rpp->alokasi_waktu_menit ?? 70); @endphp
            <section class="card p-5 sm:p-6 space-y-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold">Langkah pembelajaran (Pembelajaran Mendalam)</h3>
                        <p class="text-xs text-ink-soft mt-1">Alokasi keseluruhan <strong>{{ $alokasiMenit }} menit</strong> ({{ max(1, (int) round($alokasiMenit / 35)) }} JP × 35 menit). Susun alur <em>Memahami → Mengaplikasikan → Merefleksikan</em>.</p>
                    </div>
                    <span id="durasi-total-badge" data-target-menit="{{ $alokasiMenit }}" class="inline-flex items-center gap-1.5 rounded-xl border border-line bg-surface-alt px-3 py-1.5 text-xs font-bold text-ink">
                        Total: <span id="durasi-total-val">0</span> / {{ $alokasiMenit }} menit
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">@foreach(['pendahuluan' => 'Pendahuluan (Mindful & Joyful)', 'inti' => 'Kegiatan Inti (Memahami & Mengaplikasi)', 'penutup' => 'Penutup & Refleksi'] as $key => $label)<div><label class="block text-xs font-semibold mb-1.5" for="durasi-{{ $key }}">Durasi {{ $label }} (menit)</label><input id="durasi-{{ $key }}" name="konten[durasi_{{ $key }}]" type="number" min="1" value="{{ $content['durasi_'.$key] ?? ($key === 'inti' ? max(15, $alokasiMenit - 20) : 10) }}" class="durasi-step-input w-full px-3 py-2 rounded-xl bg-surface-alt border border-line text-sm"></div>@endforeach</div>
                <div><label class="block text-xs font-semibold mb-1.5">Uraian Pendahuluan (Berkesadaran & Menggembirakan) <span class="text-danger">*</span></label><textarea name="konten[langkah_pendahuluan]" rows="4" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm" placeholder="Salam, doa, ice breaking/kesiapan belajar, apersepsi, pertanyaan pemantik, dan penyampaian tujuan...">{{ $content['langkah_pendahuluan'] ?? '' }}</textarea></div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3"><div><label class="block text-xs font-semibold mb-1.5">Model Pembelajaran</label><select name="konten[model_pembelajaran]" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"><option value="">Pilih model</option>@foreach(['Inquiry','Problem Based Learning','Kooperatif','Demonstrasi','Project Based Learning','Lainnya'] as $item)<option @selected(($content['model_pembelajaran'] ?? '') === $item)>{{ $item }}</option>@endforeach</select></div><div><label class="block text-xs font-semibold mb-1.5">Metode</label><input name="konten[metode_pembelajaran_text]" value="{{ implode(', ', $content['metode_pembelajaran'] ?? []) }}" placeholder="Diskusi kelompok, demonstrasi, bermain peran" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></div></div>
                <div><label class="block text-xs font-semibold mb-1.5">Tahapan Kegiatan Inti: Memahami &amp; Mengaplikasikan (satu tahap per baris)</label><textarea name="konten[langkah_inti_text]" rows="6" placeholder="Memahami (20 menit): Murid mengamati benda konkret di kelas dan membaca bahan ajar...&#10;Mengaplikasikan (25 menit): Murid berdiskusi kelompok menyelesaikan LKPD kontekstual...&#10;Mengomunikasikan (10 menit): Perwakilan kelompok mempresentasikan hasil..." class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ collect($content['langkah_inti'] ?? [])->map(fn($item) => is_array($item) ? trim(($item['nama'] ?? 'Tahap'). ' ('.($item['durasi'] ?? 0).' menit): '.($item['uraian'] ?? '')) : $item)->implode("\n") }}</textarea></div>
                <div><label class="block text-xs font-semibold mb-1.5">Uraian Penutup <span class="text-danger">*</span></label><textarea name="konten[langkah_penutup]" rows="3" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm" placeholder="Menyimpulkan materi bersama, umpan balik, apresiasi, informasi pertemuan berikutnya, dan doa...">{{ $content['langkah_penutup'] ?? '' }}</textarea></div>
                <div><label class="block text-xs font-semibold mb-1.5">Refleksi Peserta Didik &amp; Guru (Merefleksikan)</label><textarea name="konten[refleksi_pembelajaran]" rows="3" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm" placeholder="Pertanyaan refleksi murid (misal: bagian mana yang paling menyenangkan / masih sulit?) dan catatan refleksi guru...">{{ $content['refleksi_pembelajaran'] ?? '' }}</textarea></div>
            </section>
        @elseif($step === 5)
            <section class="card p-5 sm:p-6 space-y-4"><div><h3 class="text-lg font-bold">Media & sumber belajar</h3><p class="text-xs text-ink-soft mt-1">Masukkan media fisik dan tautan digital. File media menerima JPG, PNG, PDF maksimal 5 MB per file.</p></div><div><label class="block text-xs font-semibold mb-1.5">Media fisik (satu per baris)</label><textarea name="konten[media_fisik_text]" rows="4" placeholder="Buku paket Matematika kelas 4&#10;Kartu bangun datar" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ implode("\n", $content['media_fisik'] ?? []) }}</textarea></div><div><label class="block text-xs font-semibold mb-1.5">Media digital / tautan (satu item per baris)</label><textarea name="konten[media_digital_text]" rows="4" placeholder="Video pengenalan bangun datar — https://..." class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ implode("\n", $content['media_digital'] ?? []) }}</textarea></div><div><label class="block text-xs font-semibold mb-1.5" for="media-files">Berkas pendukung</label><input id="media-files" type="file" name="media_files[]" multiple accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf" class="block w-full text-sm text-ink-soft file:mr-3 file:rounded-xl file:border-0 file:bg-primary-tint file:px-4 file:py-2 file:text-xs file:font-bold file:text-primary"><p class="text-[11px] text-ink-soft mt-1">Maksimal 5 MB per berkas.</p>@if(!empty($content['file_uploads']))<ul class="text-xs mt-2 list-disc pl-5">@foreach($content['file_uploads'] as $file)<li>{{ $file['name'] ?? basename($file['path']) }}</li>@endforeach</ul>@endif</div></section>
        @elseif($step === 6)
            <section class="card p-5 sm:p-6 space-y-5"><div><h3 class="text-lg font-bold">Rencana asesmen</h3><p class="text-xs text-ink-soft mt-1">Tentukan metode dan instrumen asesmen autentik (<em>Assessment as, for, and of Learning</em>).</p></div>@foreach(['diagnostik' => 'Diagnostik / Asesmen Awal', 'formatif' => 'Formatif (Assessment for/as Learning — Proses)', 'sumatif' => 'Sumatif (Assessment of Learning — Akhir)'] as $key => $label)<fieldset class="rounded-xl border border-line p-4 space-y-3"><legend class="px-1 text-sm font-bold">{{ $label }}</legend><label class="block text-xs font-semibold">Metode<input name="konten[asesmen_{{ $key }}][metode]" value="{{ $content['asesmen_'.$key]['metode'] ?? '' }}" placeholder="Observasi, tes lisan, lembar kerja, unjuk kerja, penilaian diri..." class="mt-1.5 w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm"></label><label class="block text-xs font-semibold">Instrumen / rubrik<textarea name="konten[asesmen_{{ $key }}][instrumen]" rows="3" placeholder="Tuliskan pertanyaan, rubrik, atau kriteria penilaian..." class="mt-1.5 w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ $content['asesmen_'.$key]['instrumen'] ?? '' }}</textarea></label></fieldset>@endforeach</section>
        @elseif($step === 7)
            <section class="card p-5 sm:p-6 space-y-5"><div><h3 class="text-lg font-bold">Diferensiasi pembelajaran</h3><p class="text-xs text-ink-soft mt-1">Pilih jenis yang diterapkan dan jelaskan dukungan untuk kebutuhan murid.</p></div>@foreach(['konten' => 'Diferensiasi Konten', 'proses' => 'Diferensiasi Proses', 'produk' => 'Diferensiasi Produk'] as $key => $label)<label class="block rounded-xl border border-line p-4"><span class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="konten[diferensiasi_jenis][]" value="{{ $key }}" @checked(in_array($key, $content['diferensiasi_jenis'] ?? [], true)) class="rounded border-line text-primary">{{ $label }}</span><textarea name="konten[diferensiasi_{{ $key }}]" rows="3" placeholder="Jelaskan bentuk diferensiasi {{ strtolower($label) }}..." class="mt-3 w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm">{{ $content['diferensiasi_'.$key] ?? '' }}</textarea></label>@endforeach</section>
        @else
            <section class="card p-5 sm:p-6 space-y-5"><div><h3 class="text-lg font-bold">Periksa sebelum mengajukan</h3><p class="text-xs text-ink-soft mt-1">Tinjau isi RPP. Setelah diajukan, Anda tidak dapat mengubahnya sampai Admin meminta revisi.</p></div><dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm"><div class="p-3 rounded-xl bg-surface-alt"><dt class="text-[11px] text-ink-soft">Topik</dt><dd class="font-semibold" data-review="judul">{{ $rpp->judul_rpp }}</dd></div><div class="p-3 rounded-xl bg-surface-alt"><dt class="text-[11px] text-ink-soft">Mata Pelajaran / Fase</dt><dd class="font-semibold">{{ $rpp->mataPelajaran->nama_mapel ?? 'Pilih pada Informasi Umum' }} / {{ $rpp->fase ?? '-' }}</dd></div><div class="p-3 rounded-xl bg-surface-alt"><dt class="text-[11px] text-ink-soft">Alokasi</dt><dd class="font-semibold">{{ $rpp->alokasi_waktu ?? ((int) ($rpp->alokasi_waktu_menit / 35).' JP') }} (1 JP = 35 menit)</dd></div><div class="p-3 rounded-xl bg-surface-alt"><dt class="text-[11px] text-ink-soft">Jumlah pertemuan</dt><dd class="font-semibold">{{ $meetingCount }}</dd></div></dl>
            <div class="space-y-2.5">
                <h4 class="text-xs font-bold uppercase tracking-wider text-ink-soft">Checklist Kesiapan Pengajuan per Pertemuan</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($rpp->sessions ?? [] as $sess)
                        @php
                            $sc = (array) ($sess->konten ?? []);
                            $tujuanValid = collect($sc['tp_list'] ?? [])->filter(fn ($tp) => trim((string) ($tp['teks'] ?? '')) !== '');
                            $dimensiValid = $tujuanValid->isNotEmpty() && $tujuanValid->every(fn ($tp) => !empty($tp['dimensi']));
                            $checks = [
                                ['label' => 'Capaian Pembelajaran (CP)', 'ok' => !empty($sc['cp_id']), 'step' => 2],
                                ['label' => 'Minimal 3 Tujuan Pembelajaran', 'ok' => $tujuanValid->count() >= 3, 'step' => 2],
                                ['label' => 'Dimensi Profil Pelajar Pancasila', 'ok' => $dimensiValid, 'step' => 2],
                                ['label' => 'Pertanyaan Pemantik', 'ok' => !empty(array_filter($sc['pertanyaan_pemantik'] ?? [])), 'step' => 3],
                                ['label' => 'Uraian Pendahuluan & Penutup', 'ok' => !empty(trim((string) ($sc['langkah_pendahuluan'] ?? ''))) && !empty(trim((string) ($sc['langkah_penutup'] ?? ''))), 'step' => 4],
                                ['label' => 'Instrumen Asesmen (Formatif/Sumatif)', 'ok' => !empty($sc['asesmen_formatif']['instrumen'] ?? '') || !empty($sc['asesmen_sumatif']['instrumen'] ?? ''), 'step' => 6],
                            ];
                            $siap = collect($checks)->every(fn ($c) => $c['ok']);
                        @endphp
                        <div class="rounded-xl border {{ $siap ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40' }} p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-ink">Pertemuan {{ $sess->nomor_pertemuan }}</span>
                                <span class="text-[11px] font-bold {{ $siap ? 'text-emerald-700' : 'text-amber-700' }}">{{ $siap ? 'Siap diajukan' : 'Belum lengkap' }}</span>
                            </div>
                            <ul class="space-y-1 text-[11px]">
                                @foreach($checks as $chk)
                                    <li class="flex items-center justify-between gap-2">
                                        <span class="flex items-center gap-1.5 {{ $chk['ok'] ? 'text-ink-soft' : 'text-amber-800 font-semibold' }}">
                                            <i class="fas {{ $chk['ok'] ? 'fa-circle-check text-emerald-600' : 'fa-circle-exclamation text-amber-600' }}" aria-hidden="true"></i>
                                            {{ $chk['label'] }}
                                        </span>
                                        @if(!$chk['ok'])
                                            <a href="{{ route('guru.rpp.wizard.edit', [$rpp->id_rpp, 'step' => $chk['step'], 'pertemuan' => $sess->nomor_pertemuan]) }}" class="text-primary font-semibold hover:underline">Lengkapi</a>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="rounded-xl border border-info/20 bg-info-tint p-4 text-xs text-ink"><strong class="block mb-1">Syarat pengajuan</strong>Setiap pertemuan harus memiliki CP aktif, minimal 3 TP dengan dimensi Profil Pelajar Pancasila, pertanyaan pemantik, uraian pembelajaran, dan instrumen asesmen.</div>@if($rpp->catatan->isNotEmpty())<div class="rounded-xl border border-warning/20 bg-warning-tint p-4 text-xs"><strong class="block mb-2">Catatan Admin sebelumnya</strong>@foreach($rpp->catatan as $catatan)<p class="mb-2">{{ $catatan->isi }}</p>@endforeach</div>@endif</section>
        @endif
    </form>

    <div class="sticky bottom-3 z-20 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-line bg-white/95 p-3 shadow-sm backdrop-blur">
        <button type="button" id="save-draft" class="px-4 py-2.5 rounded-xl border border-line bg-white text-xs font-bold hover:bg-surface-alt"><i class="fas fa-floppy-disk mr-1" aria-hidden="true"></i>Simpan Draf</button>
        <div class="flex items-center gap-2 ml-auto">
            @if($step > 1)<a href="{{ route('guru.rpp.wizard.edit', [$rpp->id_rpp ?? 0, 'step' => $step - 1, 'pertemuan' => $nomorPertemuan]) }}" class="px-4 py-2.5 rounded-xl border border-line bg-white text-xs font-bold hover:bg-surface-alt">Sebelumnya</a>@endif
            @if($step < 8)<a href="{{ route('guru.rpp.wizard.edit', [$rpp->id_rpp ?? 0, 'step' => $step + 1, 'pertemuan' => $nomorPertemuan]) }}" id="next-step" class="px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover">Simpan & Lanjut <i class="fas fa-arrow-right ml-1" aria-hidden="true"></i></a>@else<form method="POST" action="{{ route('guru.rpp.wizard.submit', $rpp->id_rpp ?? 0) }}" id="submit-form">@csrf<button type="submit" class="px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary-hover"><i class="fas fa-paper-plane mr-1" aria-hidden="true"></i>Ajukan ke Admin</button></form>@endif
        </div>
    </div>
</div>

<script>
(() => {
    const root = document.getElementById('rpp-wizard');
    const form = document.getElementById('wizard-form');
    const saveStatus = document.getElementById('save-status');
    const saveDot = document.getElementById('save-indicator');
    const rppId = root.dataset.rppId;
    const step = Number(root.dataset.step);
    const meeting = Number(root.dataset.meeting);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('[name="_token"]').value;
    const endpoint = `{{ url('/guru/rpp') }}/${rppId}/wizard/autosave`;
    let timer;
    let dirty = false;
    const setState = (text, color) => { saveStatus.textContent = text; saveDot.className = `w-2 h-2 rounded-full ${color}`; };
    const list = (value) => (value || '').split(/\r?\n/).map(v => v.trim()).filter(Boolean);

    function collectContent() {
        const data = new FormData(form);
        const result = {};
        for (const [key, value] of data.entries()) {
            if (!key.startsWith('konten[') || key === 'konten[cp_id]') continue;
            const match = key.match(/^konten\[([^\]]+)\](?:\[([^\]]+)\])?(?:\[\])?$/);
            if (!match) continue;
            const [, parent, child] = match;
            if (parent === 'tp_list') continue;
            if (child) {
                result[parent] ||= {};
                if (key.endsWith('[]')) (result[parent][child] ||= []).push(value);
                else result[parent][child] = value;
            } else if (key.endsWith('[]')) {
                (result[parent] ||= []).push(value);
            } else result[parent] = value;
        }
        const tpMap = new Map();
        form.querySelectorAll('[name^="konten[tp_list]"]').forEach((el) => {
            const match = el.name.match(/tp_list\]\[(\d+)\]\[(teks|dimensi)\]/);
            if (!match) return;
            const [, index, field] = match;
            tpMap.set(index, tpMap.get(index) || {teks: '', dimensi: []});
            if (field === 'teks') tpMap.get(index).teks = el.value;
            else if (el.checked) tpMap.get(index).dimensi.push(el.value);
        });
        result.tp_list = [...tpMap.values()];
        const splitFields = {pertanyaan_pemantik_text: 'pertanyaan_pemantik', media_fisik_text: 'media_fisik', media_digital_text: 'media_digital', metode_pembelajaran_text: 'metode_pembelajaran', langkah_inti_text: 'langkah_inti'};
        Object.entries(splitFields).forEach(([source, target]) => {
            if (result[source] === undefined) return;
            const values = list(result[source]);
            if (target === 'metode_pembelajaran') result[target] = values;
            else if (target === 'langkah_inti') result[target] = values.map((uraian, index) => ({nama: `Tahap ${index + 1}`, uraian, durasi: 0}));
            else result[target] = values;
            delete result[source];
        });
        const cp = document.getElementById('cp-select');
        if (cp) {
            const option = cp.options[cp.selectedIndex];
            result.cp_id = cp.value || '';
            result.cp_text = option?.dataset.text || '';
            result.cp_elemen = option?.dataset.elemen || '';
            document.getElementById('cp-id').value = cp.value || '';
        } else result.cp_id = document.getElementById('cp-id').value || '';
        result.durasi = {
            pendahuluan: form.querySelector('[name="konten[durasi_pendahuluan]"]')?.value || '',
            inti: form.querySelector('[name="konten[durasi_inti]"]')?.value || '',
            penutup: form.querySelector('[name="konten[durasi_penutup]"]')?.value || '',
        };
        return result;
    }
    function collectHeader() {
        const data = new FormData(form);
        const value = (name) => data.get(name);
        return {
            id_mapel: value('id_mapel'),
            id_rooms: value('id_rooms'),
            id_bab: value('id_bab') || null,
            judul_rpp: value('judul_rpp'),
            semester: value('semester'),
            tahun_ajaran: value('tahun_ajaran'),
            alokasi_jp: value('alokasi_jp'),
            total_pertemuan: value('total_pertemuan'),
            target_jadwal: value('target_jadwal') || null,
            ruang: value('ruang') || null,
            target_tanggal: value('target_tanggal') || null,
        };
    }
    function withStep(url, wantedStep) {
        const target = new URL(url, window.location.origin);
        if (wantedStep) target.searchParams.set('step', wantedStep);
        return target.toString();
    }
    async function saveDraft(wantedStep) {
        if (!rppId) {
            setState('Membuat draf…', 'bg-warning');
            const data = collectHeader();
            data.action = 'draft';
            const response = await fetch(`{{ route('guru.rpp.wizard.store') }}`, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(data)});
            const body = await response.json().catch(() => ({}));
            if (!response.ok) {
                setState('Gagal membuat draf — periksa isian', 'bg-danger');
                return false;
            }
            window.location.href = withStep(body.redirect, wantedStep);
            return true;
        }
        setState('Menyimpan…', 'bg-warning');
        if (form.querySelector('#id_mapel')) {
            const headerResponse = await fetch(`{{ url('/guru/rpp') }}/${rppId}/wizard/header`, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(collectHeader())});
            if (!headerResponse.ok) {
                const body = await headerResponse.json().catch(() => ({}));
                setState(body.message || 'Informasi umum belum valid', 'bg-danger');
                return false;
            }
        }
        const payload = {pertemuan: meeting, konten: collectContent()};
        const response = await fetch(endpoint, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json'}, body: JSON.stringify(payload)});
        if (!response.ok) { setState('Belum tersimpan — coba lagi', 'bg-danger'); return false; }
        dirty = false;
        const result = await response.json();
        setState(`Tersimpan ${result.saved_at}`, 'bg-success');
        return true;
    }
    function scheduleSave() {
        dirty = true;
        setState('Perubahan belum disimpan', 'bg-warning');
        clearTimeout(timer);
        timer = setTimeout(() => saveDraft().catch(() => setState('Belum tersimpan — periksa koneksi', 'bg-danger')), 700);
    }
    form.addEventListener('input', scheduleSave);
    form.addEventListener('change', scheduleSave);
    document.getElementById('save-draft').addEventListener('click', () => saveDraft().catch(() => setState('Belum tersimpan — periksa koneksi', 'bg-danger')));
    const next = document.getElementById('next-step');
    next?.addEventListener('click', async (event) => {
        event.preventDefault();
        if (!rppId) {
            await saveDraft(step + 1);
            return;
        }
        const saved = await saveDraft();
        if (saved) window.location.href = next.href;
    });
    document.querySelectorAll('[data-wizard-step-link]').forEach((link) => {
        link.addEventListener('click', async (event) => {
            if (!dirty) return;
            event.preventDefault();
            const saved = await saveDraft(Number(link.dataset.wizardStepLink));
            if (saved) window.location.href = link.href;
        });
    });
    document.getElementById('submit-form')?.addEventListener('submit', async (event) => { event.preventDefault(); if (dirty) { const saved = await saveDraft(8); if (!saved) return; } if (confirm('Ajukan Modul Ajar ini untuk ditinjau Admin? Anda tidak dapat mengubahnya sampai diminta revisi.')) event.target.submit(); });
    document.getElementById('cp-select')?.addEventListener('change', (event) => {
        const option = event.target.options[event.target.selectedIndex];
        document.getElementById('cp-text').textContent = option.dataset.text || 'Pilih CP dari master CP.';
        document.getElementById('cp-id').value = event.target.value;
        scheduleSave();
    });
    document.getElementById('nomor-pertemuan')?.addEventListener('change', (event) => { const url = new URL(window.location.href); url.searchParams.set('pertemuan', event.target.value); window.location.href = url.toString(); });
    document.getElementById('media-files')?.addEventListener('change', async (event) => {
        if (!event.target.files.length) return;
        const upload = new FormData();
        upload.append('pertemuan', meeting);
        for (const file of event.target.files) upload.append('media_files[]', file);
        setState('Mengunggah berkas…', 'bg-warning');
        const response = await fetch(`{{ url('/guru/rpp') }}/${rppId}/wizard/media`, {method: 'POST', headers: {'X-CSRF-TOKEN': csrf, 'Accept': 'application/json'}, body: upload});
        if (!response.ok) { setState('Berkas gagal diunggah', 'bg-danger'); return; }
        setState('Berkas tersimpan', 'bg-success');
        window.location.reload();
    });
    document.getElementById('id_mapel')?.addEventListener('change', scheduleSave);
    document.getElementById('id_rooms')?.addEventListener('change', (event) => { const option = event.target.options[event.target.selectedIndex]; const fase = option?.dataset.fase || ''; document.getElementById('fase-preview').textContent = fase ? `Fase ${fase} (otomatis dari kelas: A = I–II, B = III–IV, C = V–VI)` : 'Fase ditentukan otomatis dari kelas (A: kelas I–II, B: III–IV, C: V–VI).'; scheduleSave(); });
    const updateDurasiTotal = () => {
        const badge = document.getElementById('durasi-total-badge');
        const valEl = document.getElementById('durasi-total-val');
        if (!badge || !valEl) return;
        const total = [...document.querySelectorAll('.durasi-step-input')].reduce((sum, el) => sum + (parseInt(el.value, 10) || 0), 0);
        const target = parseInt(badge.dataset.targetMenit, 10) || 70;
        valEl.textContent = total;
        badge.classList.toggle('border-danger', total > target);
        badge.classList.toggle('text-danger', total > target);
        badge.classList.toggle('border-line', total <= target);
        badge.classList.toggle('text-ink', total <= target);
    };
    document.querySelectorAll('.durasi-step-input').forEach((el) => el.addEventListener('input', updateDurasiTotal));
    updateDurasiTotal();
    document.getElementById('add-tp')?.addEventListener('click', () => {
        const count = form.querySelectorAll('fieldset legend').length;
        const section = form.querySelector('section.card');
        const box = document.createElement('fieldset');
        box.className = 'rounded-xl border border-line p-4 space-y-3';
        box.innerHTML = `<legend class="px-1 text-xs font-bold text-primary">Tujuan ${count + 1}</legend><textarea name="konten[tp_list][${count}][teks]" rows="2" class="w-full px-3 py-2.5 rounded-xl bg-surface-alt border border-line text-sm" placeholder="Murid mampu..."></textarea><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">${{{ json_encode($dims) }}}.map((dim) => `<label class="flex items-center gap-2 rounded-lg bg-surface-alt px-3 py-2 text-xs"><input type="checkbox" name="konten[tp_list][${count}][dimensi][]" value="${dim}" class="rounded border-line text-primary"><span>${dim}</span></label>`).join('')}</div>`;
        document.getElementById('add-tp').before(box);
        box.querySelector('textarea').focus();
    });
    form.addEventListener('submit', (e) => e.preventDefault());
    window.addEventListener('beforeunload', (event) => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
})();
</script>
@endsection

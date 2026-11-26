@extends(session('user_type') === 'guru' ? 'layouts.guru' : 'layouts.app')

@section('page_title', 'RPP & Modul Ajar')
@section('page_subtitle', 'Kelola rencana pembelajaran dan modul ajar')

@section('content')
@php
    $babGroups = isset($babList) ? $babList->groupBy('id_mapel') : collect();
@endphp

<style>
/* ══════════════════════════════════════════════════
   RPP Guru Page — Design mengikuti warna project
   Tokens: --color-primary #13527D, --color-canvas #F8FAFC, dsb.
══════════════════════════════════════════════════ */

/* ── KPI Cards ── */
.kpi-card {
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: .875rem;
    padding: 1rem 1.125rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    transition: box-shadow .15s;
}
.kpi-card:hover { box-shadow: 0 4px 16px rgba(19,82,125,.08); }
.kpi-icon {
    width: 2.5rem; height: 2.5rem;
    border-radius: .625rem;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem;
    flex-shrink: 0;
}
.kpi-label {
    font-size: .6rem;
    font-weight: 700;
    color: var(--color-ink-muted);
    text-transform: uppercase;
    letter-spacing: .07em;
    display: block;
}
.kpi-value {
    font-size: 1.6rem;
    font-weight: 800;
    line-height: 1;
    display: block;
    margin-top: .15rem;
}
.kpi-sub {
    font-size: .65rem;
    color: var(--color-ink-soft);
    display: block;
    margin-top: .35rem;
}

/* ── Filter Panel ── */
.rpp-filter-panel {
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: .875rem;
    padding: 1rem 1.125rem;
}
.rpp-input {
    background: var(--color-canvas);
    border: 1px solid var(--color-line);
    border-radius: .625rem;
    color: var(--color-ink);
    height: 2.5rem;
    font-size: .75rem;
    font-family: inherit;
    transition: border-color .15s, box-shadow .15s;
    width: 100%;
    padding: 0 .875rem;
}
.rpp-input::placeholder { color: var(--color-ink-muted); }
.rpp-input:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(19,82,125,.12);
    background: #fff;
}
.rpp-search-wrap { position: relative; }
.rpp-search-icon {
    position: absolute; left: .875rem; top: 50%; transform: translateY(-50%);
    color: var(--color-ink-muted); font-size: .75rem; pointer-events: none;
}
.rpp-search-wrap .rpp-input { padding-left: 2.25rem; }

.rpp-icon-btn {
    width: 2.5rem; height: 2.5rem;
    border-radius: .625rem;
    border: 1px solid var(--color-line);
    background: var(--color-canvas);
    color: var(--color-ink-soft);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s;
    font-size: .75rem; flex-shrink: 0;
    text-decoration: none;
}
.rpp-icon-btn:hover { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
.rpp-icon-btn.primary { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
.rpp-icon-btn.primary:hover { background: var(--color-primary-hover); }

/* ── Cakupan Bab ── */
.rpp-cakupan {
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: .875rem;
    padding: 1rem 1.125rem;
}
.rpp-cakupan-select {
    background: var(--color-canvas);
    border: 1px solid var(--color-line);
    border-radius: .625rem;
    color: var(--color-ink);
    height: 2.25rem;
    font-size: .72rem;
    font-family: inherit;
    padding: 0 .75rem;
    min-width: 160px;
}
.rpp-cakupan-select:focus { outline: none; border-color: var(--color-primary); }
.cakupan-pill {
    display: inline-flex; align-items: center; gap: .375rem;
    padding: .3rem .7rem;
    border-radius: .5rem;
    font-size: .67rem; font-weight: 600;
    border: 1px solid;
}
.cakupan-pill.done  { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
.cakupan-pill.empty { background: var(--color-canvas); color: var(--color-ink-soft); border-color: var(--color-line); }

/* ── Section header ── */
.rpp-section-title {
    font-size: .875rem;
    font-weight: 800;
    color: var(--color-ink);
}
.rpp-count-badge {
    font-size: .7rem;
    color: var(--color-ink-soft);
}

/* ── Tabs ── */
.rpp-tabs { display: flex; gap: .5rem; flex-wrap: wrap; }
.rpp-tab {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .4rem .9rem;
    border-radius: 9999px;
    font-size: .7rem; font-weight: 700;
    border: 1px solid var(--color-line);
    background: #fff;
    color: var(--color-ink-soft);
    text-decoration: none;
    transition: all .15s;
    white-space: nowrap;
}
.rpp-tab:hover { border-color: var(--color-primary); color: var(--color-primary); background: #f0f7ff; }
.rpp-tab.active { background: var(--color-primary); border-color: var(--color-primary); color: #fff; }
.rpp-tab .tab-count { font-size: .65rem; opacity: .85; }

/* ── RPP Cards ── */
.rpp-card {
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: .875rem;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: .875rem;
    transition: border-color .15s, box-shadow .15s;
}
.rpp-card:hover {
    border-color: rgba(19,82,125,.3);
    box-shadow: 0 4px 20px rgba(19,82,125,.08);
}

/* Status badges */
.status-badge {
    display: inline-flex; align-items: center; gap: .375rem;
    padding: .28rem .7rem;
    border-radius: .5rem;
    font-size: .67rem; font-weight: 700;
    border: 1px solid;
}
.status-badge.perlu-revisi { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
.status-badge.draft        { background: #fffbeb; color: #d97706; border-color: #fde68a; }
.status-badge.menunggu     { background: #fffbeb; color: #d97706; border-color: #fde68a; }
.status-badge.siap-ajar    { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }

/* Card content */
.rpp-card-mapel {
    font-size: .7rem; font-weight: 700;
    color: var(--color-primary);
    margin-top: .5rem;
}
.rpp-card-title {
    font-size: .9375rem; font-weight: 800;
    color: var(--color-ink);
    line-height: 1.35; margin-top: .2rem;
}
.rpp-card-meta {
    font-size: .67rem;
    color: var(--color-ink-soft);
    display: flex; flex-wrap: wrap;
    gap: .2rem .45rem; align-items: center;
    margin-top: .35rem;
}
.rpp-card-meta .meta-sep { color: var(--color-ink-muted); }
.rpp-card-meta .meta-cal { display: inline-flex; align-items: center; gap: .25rem; }
.rpp-card-desc {
    font-size: .7rem;
    color: var(--color-ink-soft);
    line-height: 1.65;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.rpp-draft-notice {
    font-size: .7rem;
    color: var(--color-ink-soft);
    line-height: 1.6;
}

/* Progress */
.rpp-progress-label {
    display: flex; align-items: center; justify-content: space-between;
    font-size: .67rem; margin-bottom: .4rem;
}
.rpp-progress-label span:first-child { font-weight: 700; color: var(--color-ink-soft); }
.rpp-progress-label span:last-child  { color: var(--color-ink-muted); }
.rpp-progress-bar {
    height: .3125rem;
    background: var(--color-line);
    border-radius: 9999px;
    overflow: hidden;
}
.rpp-progress-bar .fill {
    height: 100%; border-radius: 9999px; transition: width .4s ease;
}
.fill-primary { background: linear-gradient(90deg, var(--color-primary), var(--color-primary-soft)); }
.fill-success { background: linear-gradient(90deg, #16a34a, #22c55e); }
.fill-warning { background: linear-gradient(90deg, var(--color-accent-deep), var(--color-accent)); }

/* Card footer */
.rpp-card-footer {
    display: flex; align-items: center; justify-content: space-between;
    gap: .5rem; flex-wrap: wrap;
    padding-top: .875rem;
    border-top: 1px solid var(--color-line);
    margin-top: auto;
}
.rpp-btn {
    display: inline-flex; align-items: center; gap: .375rem;
    padding: .45rem .875rem;
    border-radius: .5rem;
    font-size: .68rem; font-weight: 700;
    border: 1px solid var(--color-line);
    background: #fff;
    color: var(--color-ink-soft);
    text-decoration: none; cursor: pointer;
    transition: all .15s; white-space: nowrap;
    font-family: inherit;
}
.rpp-btn:hover { background: var(--color-canvas); border-color: rgba(19,82,125,.3); color: var(--color-primary); }
.rpp-btn.cta-primary {
    background: var(--color-primary); border-color: var(--color-primary);
    color: #fff;
}
.rpp-btn.cta-primary:hover { background: var(--color-primary-hover); }
.rpp-btn.cta-revision {
    background: #fef2f2; border-color: #fecaca; color: #dc2626;
}
.rpp-btn.cta-revision:hover { background: #fee2e2; }
.rpp-btn.cta-success {
    background: #f0fdf4; border-color: #bbf7d0; color: #16a34a;
}
.rpp-btn.cta-success:hover { background: #dcfce7; }
.rpp-btn .btn-arrow { font-size: .65rem; }

/* More dropdown */
.rpp-more-wrap { position: relative; flex-shrink: 0; }
.rpp-more-btn {
    width: 2rem; height: 2rem;
    background: #fff; border: 1px solid var(--color-line);
    border-radius: .5rem;
    display: flex; align-items: center; justify-content: center;
    color: var(--color-ink-muted); cursor: pointer;
    font-size: .75rem; font-family: inherit;
    transition: background .15s, border-color .15s;
}
.rpp-more-btn:hover { background: var(--color-canvas); border-color: rgba(19,82,125,.3); color: var(--color-primary); }
.rpp-dropdown {
    position: absolute; right: 0; top: 2.25rem; z-index: 50;
    background: #fff;
    border: 1px solid var(--color-line);
    border-radius: .625rem;
    box-shadow: 0 8px 24px rgba(15,23,42,.12);
    padding: .375rem;
    min-width: 11rem;
    display: none;
}
.rpp-dropdown.open { display: block; }
.rpp-dropdown a, .rpp-dropdown button {
    display: flex; align-items: center; gap: .5rem;
    width: 100%; text-align: left;
    padding: .475rem .75rem;
    border-radius: .4rem;
    font-size: .7rem; font-weight: 600;
    color: var(--color-ink-soft);
    text-decoration: none; background: none; border: none;
    cursor: pointer; font-family: inherit;
    transition: background .12s, color .12s;
}
.rpp-dropdown a:hover, .rpp-dropdown button:hover {
    background: var(--color-canvas); color: var(--color-ink);
}
.rpp-dropdown .danger:hover { background: #fef2f2; color: #dc2626; }
.rpp-dropdown hr { border: none; border-top: 1px solid var(--color-line); margin: .25rem 0; }

/* Create button */
.rpp-btn-create {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1rem;
    border-radius: .625rem;
    background: var(--color-primary); color: #fff;
    font-size: .72rem; font-weight: 700;
    text-decoration: none; border: none; cursor: pointer;
    font-family: inherit; transition: background .15s;
}
.rpp-btn-create:hover { background: var(--color-primary-hover); }

/* Empty state */
.rpp-empty {
    background: #fff; border: 1px solid var(--color-line);
    border-radius: .875rem;
    padding: 3rem; text-align: center;
    grid-column: 1 / -1;
}

/* Responsive cards grid */
.rpp-cards-grid { grid-template-columns: repeat(1, 1fr); }
@media (min-width: 1024px) {
    .rpp-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>

<div class="space-y-5 pb-6">

    {{-- ===== HEADER BAR ===== --}}
    <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap">
        <div>
            <h2 style="font-size:.9375rem;font-weight:800;color:var(--color-ink)">RPP &amp; Modul Ajar</h2>
            <p style="font-size:.7rem;color:var(--color-ink-soft);margin-top:.2rem">Pantau status pengajuan dan kelengkapan modul pembuatanmu.</p>
        </div>
        <button type="button" onclick="openModalTambahRpp()" class="rpp-btn-create">
            <i class="fas fa-plus" aria-hidden="true"></i>
            <span>Buat Modul Ajar</span>
        </button>
    </div>

    {{-- ===== KPI CARDS ===== --}}
    <section class="grid grid-cols-2 xl:grid-cols-4 gap-3" aria-label="Ringkasan RPP">
        <div class="kpi-card">
            <div>
                <span class="kpi-label">Total Modul</span>
                <strong class="kpi-value" style="color:var(--color-ink)">{{ $kpiGuru['total'] }}</strong>
                <span class="kpi-sub">Modul tersimpan</span>
            </div>
            <div class="kpi-icon" style="background:#eff6ff;color:var(--color-primary)">
                <i class="fas fa-file-lines"></i>
            </div>
        </div>
        <div class="kpi-card">
            <div>
                <span class="kpi-label">Siap Ajar</span>
                <strong class="kpi-value" style="color:#16a34a">{{ $kpiGuru['siap_ajar'] }}</strong>
                <span class="kpi-sub">Siap digunakan</span>
            </div>
            <div class="kpi-icon" style="background:#f0fdf4;color:#16a34a">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
        <div class="kpi-card">
            <div>
                <span class="kpi-label">Menunggu Admin</span>
                <strong class="kpi-value" style="color:var(--color-accent-deep)">{{ $kpiGuru['menunggu_review'] }}</strong>
                <span class="kpi-sub">Sedang ditinjau</span>
            </div>
            <div class="kpi-icon" style="background:#fffbeb;color:var(--color-accent-deep)">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="kpi-card">
            <div>
                <span class="kpi-label">Perlu Tindakan</span>
                <strong class="kpi-value" style="color:#dc2626">{{ $kpiGuru['perlu_dilengkapi'] }}</strong>
                <span class="kpi-sub">{{ $kpiGuru['perlu_revisi'] }} revisi · {{ $kpiGuru['draft'] }} draf</span>
            </div>
            <div class="kpi-icon" style="background:#fef2f2;color:#dc2626">
                <i class="fas fa-pen-to-square"></i>
            </div>
        </div>
    </section>

    {{-- ===== CAKUPAN BAB ===== --}}
    @if($babGroups->isNotEmpty())
    <div class="rpp-cakupan">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin-bottom:.875rem">
            <div>
                <h3 style="font-size:.8rem;font-weight:700;color:var(--color-ink);display:flex;align-items:center;gap:.5rem">
                    <i class="fas fa-list-check" style="color:var(--color-primary)"></i> Cakupan Bab
                </h3>
                <p style="font-size:.65rem;color:var(--color-ink-soft);margin-top:.2rem">Bab dikelompokkan berdasarkan mata pelajaran.</p>
            </div>
            <select id="cakupan-mapel-filter" class="rpp-cakupan-select">
                <option value="semua">Semua Mata Pelajaran</option>
                @foreach($babGroups as $mapelId => $babDalamMapel)
                    @php $mapelCakupan = $babDalamMapel->first()?->mataPelajaran; @endphp
                    @if($mapelCakupan)
                        <option value="{{ $mapelId }}">{{ $mapelCakupan->nama_mapel }}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div id="cakupan-bab-groups" class="space-y-3" aria-live="polite">
            @foreach($babGroups as $mapelId => $babDalamMapel)
                @php
                    $mapelCakupan  = $babDalamMapel->first()?->mataPelajaran;
                    $babSudahAdaRpp = $babDalamMapel->filter(fn ($b) => $rppPerBab->has($b->id_bab))->count();
                @endphp
                <div class="cakupan-bab-group" data-mapel-id="{{ $mapelId }}">
                    <div style="display:flex;align-items:baseline;gap:.5rem;margin-bottom:.5rem">
                        <span style="font-size:.72rem;font-weight:700;color:var(--color-ink)">{{ $mapelCakupan?->nama_mapel ?? 'Mata Pelajaran' }}</span>
                        <span style="font-size:.65rem;color:var(--color-ink-muted)">{{ $babSudahAdaRpp }}/{{ $babDalamMapel->count() }} bab</span>
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:.4rem">
                        @foreach($babDalamMapel as $bab)
                            @php $sudah = $rppPerBab->get($bab->id_bab); @endphp
                            <span class="cakupan-pill {{ $sudah ? 'done' : 'empty' }}"
                                  title="{{ $sudah ? 'RPP: '.$sudah->judul_rpp.' ('.$sudah->status.')' : 'Belum ada RPP untuk bab ini' }}">
                                <i class="fas {{ $sudah ? 'fa-circle-check' : 'fa-circle-dashed' }}"></i>
                                {{ $bab->nama_bab }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ===== FILTER PANEL ===== --}}
    <form action="{{ route('guru.rpp.index') }}" method="GET" class="rpp-filter-panel">
        <input type="hidden" name="tab" value="{{ $tab ?? 'semua' }}">

        {{-- Baris 1: Search + Filter Hari --}}
        <div style="display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:center;margin-bottom:.75rem">
            <div class="rpp-search-wrap">
                <i class="fas fa-search rpp-search-icon"></i>
                <input type="search" name="search" value="{{ request('search') }}"
                       placeholder="Cari judul, topik, atau mapel"
                       class="rpp-input">
            </div>
            <select name="hari" onchange="this.form.submit()" class="rpp-input" style="width:auto;min-width:148px">
                <option value="semua">Semua Hari</option>
                @foreach(['Senin','Selasa','Rabu','Kamis','Jumat'] as $hari)
                    <option value="{{ $hari }}" {{ request('hari') === $hari ? 'selected' : '' }}>{{ $hari }}</option>
                @endforeach
            </select>
        </div>

        {{-- Baris 2: Kelas + Mapel + Action --}}
        <div style="display:grid;grid-template-columns:1fr 1fr auto;gap:.75rem;align-items:center">
            <select name="id_rooms" onchange="this.form.submit()" class="rpp-input">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id_rooms }}" {{ request('id_rooms') == $kelas->id_rooms ? 'selected' : '' }}>
                        {{ $kelas->pararel ?? 'Kelas '.$kelas->id_rooms }}
                    </option>
                @endforeach
            </select>
            <select name="id_mapel" onchange="this.form.submit()" class="rpp-input">
                <option value="">Semua Mapel</option>
                @foreach($mapelList as $mapel)
                    <option value="{{ $mapel->id_mapel }}" {{ request('id_mapel') == $mapel->id_mapel ? 'selected' : '' }}>
                        {{ $mapel->nama_mapel }}
                    </option>
                @endforeach
            </select>
            <div style="display:flex;gap:.5rem">
                <button type="submit" aria-label="Terapkan filter" class="rpp-icon-btn primary">
                    <i class="fas fa-filter"></i>
                </button>
                @if(request()->hasAny(['search','hari','id_rooms','id_mapel']))
                    <a href="{{ route('guru.rpp.index', array_filter(['tab' => $tab ?? null])) }}"
                       aria-label="Reset filter" class="rpp-icon-btn">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ===== DAFTAR MODUL AJAR ===== --}}
    <div>
        {{-- Header + count --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.875rem;flex-wrap:wrap">
            <span class="rpp-section-title">Daftar Modul Ajar</span>
            <span class="rpp-count-badge">{{ $rppList->total() }} modul ditemukan</span>
        </div>

        {{-- Tab pills --}}
        <div class="rpp-tabs" style="margin-bottom:1rem">
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'semua'])) }}"
               class="rpp-tab {{ ($tab ?? 'semua') === 'semua' ? 'active' : '' }}">
                Semua <span class="tab-count">{{ $kpiGuru['total'] }}</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'menunggu'])) }}"
               class="rpp-tab {{ ($tab ?? '') === 'menunggu' ? 'active' : '' }}">
                Menunggu Admin <span class="tab-count">{{ $kpiGuru['menunggu_review'] }}</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'disetujui'])) }}"
               class="rpp-tab {{ ($tab ?? '') === 'disetujui' ? 'active' : '' }}">
                Siap Ajar <span class="tab-count">{{ $kpiGuru['siap_ajar'] }}</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'revisi'])) }}"
               class="rpp-tab {{ ($tab ?? '') === 'revisi' ? 'active' : '' }}">
                Perlu Revisi <span class="tab-count">{{ $kpiGuru['perlu_revisi'] }}</span>
            </a>
            <a href="{{ route('guru.rpp.index', array_merge(request()->except('tab'), ['tab' => 'draft'])) }}"
               class="rpp-tab {{ ($tab ?? '') === 'draft' ? 'active' : '' }}">
                Draf <span class="tab-count">{{ $kpiGuru['draft'] }}</span>
            </a>
        </div>

        {{-- Cards grid --}}
        <div class="grid rpp-cards-grid gap-4">
            @forelse($rppList as $rpp)
                @php
                    $status      = $rpp->status;
                    $isDraft     = $status === 'draft';
                    $isPending   = $status === 'menunggu_review';
                    $isApproved  = $status === 'terverifikasi';
                    $isRevision  = $status === 'perlu_revisi';
                    $namaMapel   = $rpp->mataPelajaran->nama_mapel ?? 'Mata Pelajaran';
                    $wajibKurang = $rpp->komponenWajibKurang();
                    $totalWajib  = count(\App\Features\Models\Rpp::KOMPONEN_WAJIB);
                    $wajibTerisi = $totalWajib - $wajibKurang->count();
                    $pct         = $rpp->skorKelengkapan();
                    $fillClass   = $wajibKurang->isEmpty() ? 'fill-success' : ($pct >= 60 ? 'fill-warning' : 'fill-primary');
                @endphp

                <article class="rpp-card">
                    {{-- Top: badge + title + more menu --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem">
                        <div style="min-width:0;flex:1">
                            {{-- Status badge --}}
                            @if($isRevision)
                                <span class="status-badge perlu-revisi">
                                    <i class="fas fa-triangle-exclamation"></i> Perlu Revisi
                                </span>
                            @elseif($isPending)
                                <span class="status-badge menunggu">
                                    <i class="fas fa-clock"></i> Menunggu Admin
                                </span>
                            @elseif($isApproved)
                                <span class="status-badge siap-ajar">
                                    <i class="fas fa-circle-check"></i> Siap Ajar
                                </span>
                            @else
                                <span class="status-badge draft">
                                    <i class="fas fa-pencil"></i> Draf
                                </span>
                            @endif

                            {{-- Mapel · Kelas --}}
                            <p class="rpp-card-mapel">{{ $namaMapel }} · {{ $rpp->kelas->pararel ?? '-' }}</p>

                            {{-- Judul --}}
                            <h4 class="rpp-card-title">{{ $rpp->judul_rpp }}</h4>

                            {{-- Meta info --}}
                            <div class="rpp-card-meta">
                                <span>{{ $rpp->fase ?? 'Fase -' }}</span>
                                <span class="meta-sep" aria-hidden="true">·</span>
                                <span>{{ $rpp->bab->nama_bab ?? '-' }}</span>
                                <span class="meta-sep" aria-hidden="true">·</span>
                                @if(!$isDraft && $rpp->target_jadwal)
                                    <span class="meta-cal">
                                        <i class="far fa-calendar-days"></i>
                                        {{ $rpp->target_jadwal }}
                                    </span>
                                    <span class="meta-sep" aria-hidden="true">·</span>
                                    <span>{{ $rpp->alokasi_waktu ?? '2 JP' }}</span>
                                @else
                                    <span>Jadwal belum ditentukan</span>
                                @endif
                            </div>
                        </div>

                        {{-- ··· More menu --}}
                        <div class="rpp-more-wrap">
                            <button type="button" class="rpp-more-btn"
                                    onclick="toggleRppDropdown(this)" aria-label="Buka tindakan modul">
                                <i class="fas fa-ellipsis" aria-hidden="true"></i>
                            </button>
                            <div class="rpp-dropdown">
                                <button type="button"
                                        onclick="openModalDetailRpp({{ $rpp->id_rpp }});this.closest('.rpp-dropdown').classList.remove('open')">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </button>
                                @if(in_array($rpp->status, ['draft','perlu_revisi'], true))
                                    <a href="{{ route('guru.rpp.wizard.edit', ['id' => $rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]) }}">
                                        <i class="fas fa-pen"></i> Edit di Wizard
                                    </a>
                                @endif
                                <a href="{{ route('guru.rpp.download', $rpp->id_rpp) }}" target="_blank">
                                    <i class="fas fa-download"></i> Unduh / Cetak
                                </a>
                                <hr>
                                <form action="{{ route('guru.rpp.destroy', $rpp->id_rpp) }}" method="POST"
                                      onsubmit="return confirm('Hapus RPP ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="danger">
                                        <i class="fas fa-trash-can"></i> Hapus Modul
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi / catatan draft --}}
                    @if($isDraft)
                        <p class="rpp-draft-notice">
                            Lengkapi tujuan pembelajaran, langkah kegiatan, dan jadwal
                            sebelum mengirim modul untuk ditinjau admin.
                        </p>
                    @elseif($rpp->deskripsi)
                        <p class="rpp-card-desc">{{ $rpp->deskripsi }}</p>
                    @endif

                    {{-- Catatan revisi --}}
                    @if($isRevision && $rpp->catatan_revisi)
                        <p style="font-size:.68rem;color:#dc2626;background:#fef2f2;border:1px solid #fecaca;border-radius:.5rem;padding:.5rem .75rem;line-height:1.55">
                            <strong>Catatan revisi:</strong> {{ $rpp->catatan_revisi }}
                        </p>
                    @endif

                    {{-- Progress bar kelengkapan --}}
                    <div>
                        <div class="rpp-progress-label">
                            <span>Kelengkapan komponen wajib</span>
                            <span>{{ $wajibTerisi }} dari {{ $totalWajib }}</span>
                        </div>
                        <div class="rpp-progress-bar" role="progressbar"
                             aria-label="Kelengkapan komponen wajib"
                             aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="fill {{ $fillClass }}" style="width:{{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Footer actions --}}
                    <footer class="rpp-card-footer">
                        {{-- Kiri: Detail + Unduh --}}
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <button type="button" onclick="openModalDetailRpp({{ $rpp->id_rpp }})" class="rpp-btn">
                                <i class="fas fa-file-lines"></i> Detail
                            </button>
                            <a href="{{ route('guru.rpp.download', $rpp->id_rpp) }}" target="_blank"
                               rel="noopener" class="rpp-btn">
                                <i class="fas fa-download"></i> Unduh
                            </a>
                        </div>

                        {{-- Kanan: CTA utama --}}
                        @if($isApproved)
                            <a href="{{ route('guru.mapel.browse') }}" class="rpp-btn cta-success">
                                <i class="fas fa-chalkboard-user"></i> Mulai Mengajar
                                <i class="fas fa-arrow-right btn-arrow"></i>
                            </a>
                        @elseif($isPending)
                            <span class="rpp-btn" style="cursor:default;opacity:.65">
                                <i class="fas fa-hourglass-half"></i> Menunggu Admin
                            </span>
                        @elseif($isRevision)
                            <a href="{{ route('guru.rpp.wizard.edit', ['id' => $rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]) }}"
                               class="rpp-btn cta-revision">
                                <i class="fas fa-pen-to-square"></i> Perbaiki di Wizard
                                <i class="fas fa-arrow-right btn-arrow"></i>
                            </a>
                        @else
                            <a href="{{ route('guru.rpp.wizard.edit', ['id' => $rpp->id_rpp, 'step' => 1, 'pertemuan' => 1]) }}"
                               class="rpp-btn cta-primary">
                                <i class="fas fa-wand-magic-sparkles"></i> Lanjutkan Wizard
                                <i class="fas fa-arrow-right btn-arrow"></i>
                            </a>
                        @endif
                    </footer>
                </article>
            @empty
                <div class="rpp-empty">
                    <div style="width:3rem;height:3rem;border-radius:50%;background:var(--color-canvas);border:1px solid var(--color-line);display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.2rem;color:var(--color-ink-muted)">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4 style="font-size:.85rem;font-weight:700;color:var(--color-ink);margin-bottom:.5rem">
                        Tidak ada Modul Ajar ditemukan
                    </h4>
                    <p style="font-size:.72rem;color:var(--color-ink-soft);margin-bottom:1.25rem">
                        Coba sesuaikan filter atau buat modul ajar baru untuk semester ini.
                    </p>
                    <button type="button" onclick="openModalTambahRpp()" class="rpp-btn-create">
                        <i class="fas fa-plus"></i> Buat Modul Ajar Baru
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($rppList->hasPages())
            <div class="pt-4 flex justify-center">{{ $rppList->links() }}</div>
        @endif
    </div>

    @include('rpp_guru.modals')
</div>

{{-- ===== SCRIPTS ===== --}}
@if($babGroups->isNotEmpty())
<script>
    (() => {
        const filter = document.getElementById('cakupan-mapel-filter');
        const groups = document.querySelectorAll('#cakupan-bab-groups [data-mapel-id]');
        if (!filter) return;
        filter.addEventListener('change', () => {
            groups.forEach((g) => {
                g.hidden = filter.value !== 'semua' && g.dataset.mapelId !== filter.value;
            });
        });
    })();
</script>
@endif

<script>
    /* Toggle ··· dropdown */
    function toggleRppDropdown(btn) {
        const wrap = btn.closest('.rpp-more-wrap');
        const dd   = wrap.querySelector('.rpp-dropdown');
        document.querySelectorAll('.rpp-dropdown.open').forEach(el => {
            if (el !== dd) el.classList.remove('open');
        });
        dd.classList.toggle('open');
    }
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.rpp-more-wrap')) {
            document.querySelectorAll('.rpp-dropdown.open').forEach(el => el.classList.remove('open'));
        }
    });
</script>
@endsection

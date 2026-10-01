<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('siswa.index') }}" class="btn btn-sm btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Kembali ke Daftar Siswa">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h4 class="m-0 fw-bold fs-5 text-dark">Profil Siswa</h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if(isset($siswa) && Route::has('siswa.edit'))
            <a href="{{ route('siswa.edit', $siswa->id_siswa ?? $siswa) }}" class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Edit Data Siswa">
                <i class="bi bi-pencil"></i>
            </a>
        @endif
        <a href="{{ Route::has('notifications') ? route('notifications') : '#' }}" class="btn btn-sm btn-light border rounded-circle d-inline-flex align-items-center justify-content-center text-secondary" style="width: 36px; height: 36px;" title="Notifikasi">
            <i class="bi bi-bell"></i>
        </a>
    </div>
</div>
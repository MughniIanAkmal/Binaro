<script>
function toggleModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.contains('hidden') ? (el.classList.remove('hidden'), el.classList.add('flex')) : (el.classList.add('hidden'), el.classList.remove('flex'));
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['modal-qr', 'modal-edit'].forEach(id => {
        const m = document.getElementById(id);
        if (m && !m.classList.contains('hidden')) toggleModal(id);
    });
});
function cetakQR() {
    const img = document.getElementById('qrImage');
    if (!img) { alert('QR belum tersedia. Hubungi admin / guru.'); return; }
    const kode = @json($siswa->barcode->kode_barcode ?? '-');
    const nama = @json($namaSiswa);
    const w = window.open('', '_blank', 'width=480,height=640');
    if (!w) { alert('Popup diblokir browser. Izinkan popup untuk situs ini lalu coba lagi.'); return; }
    w.document.write('<html><head><title>Cetak QR - ' + nama + '</title><style>body{font-family:sans-serif;text-align:center;padding:32px}img{width:320px;height:320px}h2{margin:0}p{color:#555}.kode{font-weight:900;letter-spacing:.2em;font-size:20px}</style></head><body><h2>' + nama + '</h2><p>{{ $namaKelas }} &bull; {{ $nisn }}</p><img src="' + img.src + '"><p class="kode">' + kode + '</p><script>window.onload=function(){window.print()}<\/script></body></html>');
    w.document.close();
}

(function () {
    const lama = document.getElementById('password_lama');
    const baru = document.getElementById('password_baru');
    const konf = document.getElementById('password_konfirmasi');
    const form = document.getElementById('formPassword');
    if (!lama || !baru || !konf || !form) return;

    function showWarn(input, boxId, msg) {
        const box = document.getElementById(boxId);
        if (!box) return;
        const text = box.querySelector('span');
        if (msg) {
            box.classList.remove('hidden');
            if (text) text.textContent = msg;
            input.classList.add('border-rose-400');
            input.classList.remove('border-slate-200');
        } else {
            box.classList.add('hidden');
            input.classList.remove('border-rose-400');
            input.classList.add('border-slate-200');
        }
    }

    function validateLive() {
        showWarn(baru, 'warn_password', baru.value !== '' && baru.value.length < 6 ? 'Password baru minimal 6 karakter.' : '');
        showWarn(konf, 'warn_password_confirmation', konf.value !== '' && baru.value !== '' && konf.value !== baru.value ? 'Konfirmasi password baru tidak sesuai.' : '');
        if (document.activeElement === lama || lama.value !== '') {
            showWarn(lama, 'warn_password_lama', lama.value === '' ? 'Password lama wajib diisi.' : '');
        }
    }

    [lama, baru, konf].forEach(el => el.addEventListener('input', validateLive));

    form.addEventListener('submit', function (e) {
        let batal = false;
        if (lama.value.trim() === '') { showWarn(lama, 'warn_password_lama', 'Password lama wajib diisi. Diisi dulu password Anda saat ini.'); batal = true; }
        if (baru.value.length < 6) { showWarn(baru, 'warn_password', 'Password baru minimal 6 karakter.'); batal = true; }
        if (konf.value !== baru.value) { showWarn(konf, 'warn_password_confirmation', 'Konfirmasi password baru tidak sesuai. Samakan dengan password baru.'); batal = true; }
        if (batal) { e.preventDefault(); lama.classList.contains('border-rose-400') ? lama.focus() : (baru.classList.contains('border-rose-400') ? baru.focus() : konf.focus()); }
    });
})();

function cekEmailLive(input) {
    const box = document.getElementById('warn_email');
    if (!box) return;
    if (input.value !== '' && !input.value.includes('@')) {
        box.classList.remove('hidden');
        input.classList.add('border-rose-400');
    } else {
        box.classList.add('hidden');
        input.classList.remove('border-rose-400');
    }
}

(function () {
    const u = document.getElementById('edit_username');
    const hp = document.getElementById('edit_nohp');
    const em = document.getElementById('edit_email');
    const form = document.getElementById('formEdit');
    const cu = document.getElementById('count_username');
    const ch = document.getElementById('count_nohp');
    function refreshCount() {
        if (u && cu) cu.textContent = u.value.length;
        if (hp && ch) ch.textContent = hp.value.length;
    }
    if (u) u.addEventListener('input', refreshCount);
    if (hp) hp.addEventListener('input', refreshCount);
    refreshCount();
    if (!form) return;
    form.addEventListener('submit', function (e) {
        let msg = '';
        if (u && !/^(?=.*[A-Za-z])[A-Za-z ]+$/.test(u.value)) msg = 'Username hanya boleh berisi huruf (A-Z) dan spasi, maksimal 25.';
        else if (u && u.value.length > 25) msg = 'Username maksimal 25 huruf.';
        else if (em && !em.value.includes('@')) msg = 'Email harus mengandung tanda @, contoh: nama@email.com.';
        else if (hp && hp.value !== '' && (!/^[0-9]+$/.test(hp.value) || hp.value.length > 12)) msg = 'No. HP hanya angka dan maksimal 12.';
        if (msg) { e.preventDefault(); alert(msg); }
    });
})();

@if(session('open_edit') || $errors->has('username') || $errors->has('email') || $errors->has('no_hp'))
toggleModal('modal-edit');
@endif
</script>
const ENDPOINT_IZIN = @json(route('guru.absen.izin'));
const DAFTAR_SISWA = @json($daftarNama);
const elIzinHasil = document.getElementById('izinHasil');
const wrapIzinKet = document.getElementById('wrapIzinKeterangan');

function izinJenisTerpilih() {
    const r = document.querySelector('input[name="izinJenis"]:checked');
    return r ? r.value : 'Sakit';
}

document.querySelectorAll('input[name="izinJenis"]').forEach(function (r) {
    r.addEventListener('change', function () {
        wrapIzinKet.classList.toggle('hidden', izinJenisTerpilih() !== 'Izin');
    });
});

function izinPesan(tipe, pesan) {
    const base = 'px-3 py-2.5 rounded-xl text-xs font-bold text-center ';
    elIzinHasil.classList.remove('hidden');
    elIzinHasil.className = base + (tipe === 'ok' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600');
    elIzinHasil.textContent = pesan;
}

function cariSiswaDiDaftar(namaInput) {
    const m = namaInput.match(/\((\d+)\)\s*$/);
    if (m) return DAFTAR_SISWA.find(function (s) { return s.nisn === m[1]; }) || null;
    const rendah = namaInput.toLowerCase();
    const cocok = DAFTAR_SISWA.filter(function (s) { return (s.nama || '').toLowerCase() === rendah; });
    if (cocok.length === 1) return cocok[0];
    if (cocok.length > 1) return { ambigu: true, jumlah: cocok.length, contoh: cocok[0] };
    return null;
}

document.getElementById('formIzin').addEventListener('submit', async function (e) {
    e.preventDefault();
    const namaInput = document.getElementById('izinNama').value.trim();
    const jenis = izinJenisTerpilih();
    const keterangan = document.getElementById('izinKeterangan').value.trim();

    if (!namaInput) { izinPesan('err', 'Isi nama siswa terlebih dahulu.'); return; }

    const cek = cariSiswaDiDaftar(namaInput);
    if (!cek) { izinPesan('err', 'Nama tidak terdaftar. Isi ulang nama dengan benar sesuai data siswa.'); return; }
    if (cek.ambigu) { izinPesan('err', 'Ada ' + cek.jumlah + ' siswa bernama tersebut. Tulis "Nama (NISN)", contoh: ' + cek.contoh.nama + ' (' + cek.contoh.nisn + ').'); return; }
    if (jenis === 'Izin' && !keterangan) { izinPesan('err', 'Anda memilih keterangan lain: wajib mengisi kolom keterangan.'); return; }

    try {
        const res = await fetch(ENDPOINT_IZIN, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ nama: namaInput, jenis: jenis, keterangan: keterangan }),
        });
        const data = await res.json();
        if (res.ok && data.status === 'ok') {
            izinPesan('ok', data.message);
            tambahLog(data);
            document.getElementById('formIzin').reset();
            wrapIzinKet.classList.add('hidden');
        } else {
            izinPesan('err', data.message || 'Gagal menyimpan izin.');
        }
    } catch (err) {
        izinPesan('err', 'Gagal terhubung ke server.');
    }
});
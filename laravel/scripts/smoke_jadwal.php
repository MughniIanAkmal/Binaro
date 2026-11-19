<?php
/**
 * Smoke test jadwal untuk 3 peran (admin, guru, siswa).
 * Jalankan: php scripts/smoke_jadwal.php
 */
require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function requestAs(string $url, array $session): array
{
    global $kernel;

    $req = Illuminate\Http\Request::create($url, 'GET');
    $store = app('session')->driver();
    $store->start();
    foreach ($session as $k => $v) {
        $store->put($k, $v);
    }
    $req->setLaravelSession($store);

    try {
        $resp = $kernel->handle($req);
        $body = $resp->getContent();
        $status = $resp->getStatusCode();

        $isErrorPage = str_contains($body, 'Whoops') || str_contains($body, 'Symfony\\Component\\ErrorHandler');

        return [$status, $isErrorPage, strlen($body)];
    } catch (Throwable $e) {
        return ['EXCEPTION', true, substr($e->getMessage(), 0, 120)];
    }
}

$admin = ['user_id' => 1, 'user_type' => 'admin', 'user_name' => 'Administrator'];
$guruRow = App\Models\Guru::first();
$siswaRow = App\Models\Siswa::first();

// Ambil id jadwal yang benar-benar ada (hindari 404 palsu saat DB kosong).
$jadwalId = App\Models\JadwalMataPelajaran::value('id_jadwal');

$guru = $guruRow
    ? ['user_id' => $guruRow->id_guru, 'user_type' => 'guru', 'user_name' => $guruRow->nama_guru]
    : null;
$siswa = $siswaRow
    ? ['user_id' => $siswaRow->id_siswa, 'user_type' => 'siswa', 'user_name' => $siswaRow->nm_siswa]
    : null;

$cases = [
    ['ADMIN  jadwal.index',      '/jadwal',                    $admin],
    ['ADMIN  jadwal.create',     '/jadwal/create',             $admin],
    ['ADMIN  dashboard',         '/admin/dashboard',           $admin],
    ['ADMIN  absensi',           '/absensi',                   $admin],
];

if ($jadwalId) {
    $cases[] = ['ADMIN  jadwal.show', '/jadwal/'.$jadwalId,      $admin];
    $cases[] = ['ADMIN  jadwal.edit', '/jadwal/'.$jadwalId.'/edit', $admin];
}

if ($guru) {
    $cases[] = ['GURU   jadwal',            '/guru/jadwal',        $guru];
    $cases[] = ['GURU   jadwal-mengajar',   '/guru/jadwal-mengajar', $guru];
    $cases[] = ['GURU   dashboard',         '/guru/dashboard',     $guru];
    $cases[] = ['GURU   absensi',           '/guru/absensi',       $guru];
}
if ($siswa) {
    $cases[] = ['SISWA  jadwal',            '/siswa/jadwal',          $siswa];
    $cases[] = ['SISWA  jadwal-mapel',      '/siswa/jadwal-mapel',    $siswa];
    $cases[] = ['SISWA  dashboard',         '/siswa/dashboard',       $siswa];
    $cases[] = ['SISWA  mapel',             '/siswa/mapel',           $siswa];
}

$fail = 0;
foreach ($cases as [$label, $url, $sess]) {
    [$status, $isError, $extra] = requestAs($url, $sess);
    $ok = ($status === 200) && ! $isError;
    if (! $ok) {
        $fail++;
    }
    printf("%-26s %-26s %s %s\n", $label, $url, $ok ? 'OK ' : 'FAIL', $ok ? '' : "status=$status err=$extra");
}

echo "\n".($fail === 0 ? "SEMUA HALAMAN OK" : "ADA {$fail} HALAMAN GAGAL")."\n";
exit($fail === 0 ? 0 : 1);

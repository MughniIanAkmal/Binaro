<?php

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

function apiRequest($kernel, $method, $uri, $data = [], $token = null) {
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    $request->headers->set('Accept', 'application/json');
    if ($token) {
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }
    $response = $kernel->handle($request);
    $json = json_decode($response->getContent(), true);
    return [$response->getStatusCode(), $json];
}

// 1. Auth Login Siswa
$siswa = App\Models\Siswa::first();
assert($siswa !== null, 'Siswa harus ada di DB');
$siswa->password = \Illuminate\Support\Facades\Hash::make('siswa1234');
$siswa->save();

[$status, $loginRes] = apiRequest($kernel, 'POST', '/api/login', [
    'nisn' => $siswa->nisn,
    'password' => 'siswa1234',
]);
assert($status === 200, "Login Siswa gagal (Status: $status)");
assert(!empty($loginRes['data']['token']), 'Token login harus ada');
$token = $loginRes['data']['token'];

// 2. Siswa Dashboard & Profile
[$sDash, $rDash] = apiRequest($kernel, 'GET', '/api/siswa/dashboard', [], $token);
assert($sDash === 200 && ($rDash['success'] ?? false) === true, 'Dashboard API gagal');

[$sProf, $rProf] = apiRequest($kernel, 'GET', '/api/siswa/profile', [], $token);
assert($sProf === 200 && ($rProf['success'] ?? false) === true, 'Profile API gagal');

// 3. Siswa Absensi Hari Ini
[$sAbs, $rAbs] = apiRequest($kernel, 'GET', '/api/siswa/absensi-hari-ini', [], $token);
assert($sAbs === 200 && ($rAbs['success'] ?? false) === true, 'Absensi Hari Ini API gagal');

// 4. Mapel & Materi Hierarchy
[$sMapel, $rMapel] = apiRequest($kernel, 'GET', '/api/siswa/mapel', [], $token);
assert($sMapel === 200 && count($rMapel['data']['mapel']) > 0, 'Mapel API gagal');

$mapelId = $rMapel['data']['mapel'][0]['id_mapel'];
[$sBab, $rBab] = apiRequest($kernel, 'GET', "/api/siswa/mapel/{$mapelId}/bab", [], $token);
assert($sBab === 200 && ($rBab['success'] ?? false) === true, 'Bab API gagal');

// 5. Ujian Online
[$sUjian, $rUjian] = apiRequest($kernel, 'GET', '/api/siswa/ujian', [], $token);
assert($sUjian === 200 && ($rUjian['success'] ?? false) === true, 'Ujian Index API gagal');

// 6. Guru API
$guru = App\Models\Guru::first();
if ($guru) {
    $guru->password = \Illuminate\Support\Facades\Hash::make('guru1234');
    $guru->save();
    [$sGLogin, $rGLogin] = apiRequest($kernel, 'POST', '/api/login', [
        'nisn' => $guru->nip,
        'password' => 'guru1234',
    ]);
    assert($sGLogin === 200, 'Login Guru gagal');
    $gToken = $rGLogin['data']['token'];

    [$sGProf, $rGProf] = apiRequest($kernel, 'GET', '/api/guru/profile', [], $gToken);
    assert($sGProf === 200, 'Guru Profile API gagal');

    [$sGRpp, $rGRpp] = apiRequest($kernel, 'GET', '/api/guru/rpp', [], $gToken);
    assert($sGRpp === 200, 'Guru RPP API gagal');
}

echo "API SELF-CHECK PASSED: Seluruh endpoint API berjalan sempurna!\n";

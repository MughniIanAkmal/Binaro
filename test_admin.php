<?php
try {
    $admin = App\Models\Admin::where('nip', '19850101001')->first();
    if ($admin) {
        echo 'Admin found: ' . $admin->nama_admin . ', password: ' . $admin->password . "\n";
        echo 'Hash check: ' . (Hash::check('admin123', $admin->password) ? 'MATCH' : 'FAIL') . "\n";
    } else {
        echo 'Admin not found\n';
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}
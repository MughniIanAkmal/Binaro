<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userType = session('user_type');

        if (!$userType || !in_array($userType, $roles)) {
            if ($userType) {
                $target = match ($userType) {
                    'admin' => '/admin/dashboard',
                    'siswa' => '/siswa/dashboard',
                    default => '/guru/dashboard',
                };
                return redirect($target)->with('error', 'Akses ditolak. Anda tidak memiliki hak akses ke halaman tersebut.');
            }
            return redirect('/login')->with('error', 'Akses ditolak. Anda tidak memiliki hak akses.');
        }

        return $next($request);
    }
}

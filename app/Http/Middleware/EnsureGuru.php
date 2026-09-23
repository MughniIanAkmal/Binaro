<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGuru
{
    public function handle(Request $request, Closure $next)
    {
        if (session('user_type') !== 'guru') {
            abort(403, 'Fitur scan presensi hanya dapat digunakan oleh guru.');
        }

        return $next($request);
    }
}
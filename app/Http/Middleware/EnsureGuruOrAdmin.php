<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureGuruOrAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! in_array(session('user_type'), ['guru', 'admin'], true)) {
            abort(403, 'Fitur scan presensi hanya dapat digunakan oleh guru atau admin.');
        }

        return $next($request);
    }
}

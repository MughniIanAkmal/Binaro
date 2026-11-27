<?php

namespace App\Features\Middleware;

use App\Features\Models\Admin;
use App\Features\Models\Guru;
use App\Features\Models\Siswa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user('sanctum') ?? $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $role = match (true) {
            $user instanceof Siswa => 'siswa',
            $user instanceof Guru => 'guru',
            $user instanceof Admin => 'admin',
            default => null,
        };

        if (! $role || ! in_array($role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Akses ditolak untuk role ini.',
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Services\ApiTokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware
{
    /**
     * Handle an incoming request for API endpoints.
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $resolved = ApiTokenService::resolveUser($request, $requiredRole);

        if (!$resolved || empty($resolved['user'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => ['auth' => ['Token autentikasi tidak valid atau sudah kedaluwarsa.']],
            ], 401);
        }

        $user = $resolved['user'];
        $role = $resolved['role'];

        // Cek permission role jika dispesifikasikan
        if ($requiredRole && $role !== $requiredRole && $role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Akses ditolak untuk role ini.',
            ], 403);
        }

        // Attach user resolver ke Request
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        // Sinkronkan ke Session untuk mendukung controller atau helper yang membaca Session
        $userId = match ($role) {
            'siswa' => $user->id_siswa,
            'guru' => $user->id_guru,
            'admin' => $user->id_admin,
            default => $user->getKey(),
        };

        Session::put('user_id', $userId);
        Session::put('user_type', $role);

        if ($role === 'guru') {
            Session::put('guru_id', $user->id_guru);
        } elseif ($role === 'siswa') {
            Session::put('siswa_id', $user->id_siswa);
        }

        return $next($request);
    }
}

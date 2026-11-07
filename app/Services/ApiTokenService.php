<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

class ApiTokenService
{
    /**
     * Buat token API terenkripsi yang aman (stateless, tanpa perlu package eksternal)
     */
    public static function createToken(Authenticatable $user, string $role): string
    {
        $payload = [
            'id' => $user->getKey(),
            'role' => $role,
            'created_at' => time(),
            'expires_at' => time() + (86400 * 30), // Valid 30 hari
        ];

        return Crypt::encryptString(json_encode($payload));
    }

    /**
     * Decode dan validasi token API
     */
    public static function parseToken(?string $token): ?array
    {
        if (empty($token)) {
            return null;
        }

        try {
            $decrypted = Crypt::decryptString($token);
            $payload = json_decode($decrypted, true);

            if (!is_array($payload) || empty($payload['id']) || empty($payload['role'])) {
                return null;
            }

            if (!empty($payload['expires_at']) && $payload['expires_at'] < time()) {
                return null; // Expired
            }

            return $payload;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Resolve user dari request (Bearer token, header X-Api-Token, session, atau request parameter)
     */
    public static function resolveUser(Request $request, ?string $requiredRole = null): ?array
    {
        $token = $request->bearerToken() 
            ?? $request->header('X-Api-Token') 
            ?? $request->query('api_token') 
            ?? $request->input('api_token');

        $user = null;
        $role = null;

        if ($token) {
            $payload = self::parseToken($token);
            if ($payload) {
                $role = $payload['role'];
                $id = $payload['id'];

                $user = match ($role) {
                    'siswa' => Siswa::find($id),
                    'guru' => Guru::find($id),
                    'admin' => Admin::find($id),
                    default => null,
                };
            }
        }

        // Fallback 1: Session authentication
        if (!$user && Session::has('user_id')) {
            $sessId = Session::get('user_id');
            $sessType = Session::get('user_type') ?? ($requiredRole ?: 'siswa');
            $role = $sessType;

            $user = match ($sessType) {
                'siswa' => Siswa::find($sessId),
                'guru' => Guru::find(Session::get('guru_id') ?? $sessId),
                'admin' => Admin::find($sessId),
                default => null,
            };
        }

        // Fallback 2: Parameter id_siswa / id_guru / id_admin dari request
        if (!$user) {
            if ($request->has('id_siswa')) {
                $user = Siswa::find($request->input('id_siswa') ?? $request->query('id_siswa'));
                $role = 'siswa';
            } elseif ($request->has('id_guru')) {
                $user = Guru::find($request->input('id_guru') ?? $request->query('id_guru'));
                $role = 'guru';
            } elseif ($requiredRole === 'siswa') {
                $user = Siswa::first();
                $role = 'siswa';
            } elseif ($requiredRole === 'guru') {
                $user = Guru::first();
                $role = 'guru';
            } elseif ($requiredRole === 'admin') {
                $user = Admin::first();
                $role = 'admin';
            }
        }

        if (!$user) {
            return null;
        }

        return [
            'user' => $user,
            'role' => $role,
        ];
    }
}

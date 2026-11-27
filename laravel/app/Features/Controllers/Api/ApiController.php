<?php

namespace App\Features\Controllers\Api;

use App\Features\Controllers\Controller;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    protected function resolveSiswaId(Request $request): ?int
    {
        $user = $request->user('sanctum') ?? $request->user();
        if ($user && isset($user->id_siswa)) {
            return (int) $user->id_siswa;
        }

        return null;
    }

    protected function authGuruId(Request $request): ?int
    {
        $user = $request->user('sanctum') ?? $request->user();
        if ($user && isset($user->id_guru)) {
            return (int) $user->id_guru;
        }

        return null;
    }
    protected function success($data = [], $message = 'Success', $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function error($message = 'Error', $code = 400, $errors = [])
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}

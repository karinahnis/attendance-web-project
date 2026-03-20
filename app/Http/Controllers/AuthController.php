<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     * POST /api/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'pin'            => $request->pin,
            'role'           => $request->role ?? 0,
            'is_first_login' => true,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'data'    => [
                'user'         => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'role'           => $user->role,
                    'is_first_login' => $user->is_first_login,
                ],
                'token_type'   => 'Bearer',
                'access_token' => $token,
            ],
        ], 201);
    }

    /**
     * Login user dan kembalikan token.
     * POST /api/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Verifikasi credentials
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Hapus semua token lama sebelum buat token baru
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        // Jika ini bukan first login lagi, update flag
        $isFirstLogin = $user->is_first_login;
        if ($isFirstLogin) {
            $user->update(['is_first_login' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'user'         => [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'role'           => $user->role,
                    'is_first_login' => $isFirstLogin,    // nilai SEBELUM diupdate
                ],
                'token_type'   => 'Bearer',
                'access_token' => $token,
            ],
        ], 200);
    }

    /**
     * Logout user (revoke current token).
     * POST /api/auth/logout  [middleware: auth:sanctum]
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }

    /**
     * Ambil data user yang sedang login.
     * GET /api/auth/me  [middleware: auth:sanctum]
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'is_first_login' => $user->is_first_login,
            ],
        ], 200);
    }
}

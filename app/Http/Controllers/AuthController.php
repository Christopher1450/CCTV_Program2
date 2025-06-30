<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // LOGIN
    public function login(Request $request)
    {
        // Validasi awal
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 422);
        }

        $credentials = $request->only('username', 'password');

        // Proses login
        if (!$token = JWTAuth::attempt($credentials)) {
            // Log attempt here if needed
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // // Cek jika user tidak aktif (opsional)
        // if (!Auth::user()->is_active ?? true) {
        //     return response()->json(['error' => 'Account disabled'], 403);
        // }

        $refreshCookie = $this->getRefreshCookie($token);

        return response()->json([
            'token' => $token,
            'user' => Auth::user()
        ])->withCookie($refreshCookie);
    }

    // REFRESH TOKEN
    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');

            if (!$refreshToken) {
                return response()->json(['error' => 'Refresh token not found'], 401);
            }

            $newToken = JWTAuth::setToken($refreshToken)->refresh();

            return response()->json([
                'token' => $newToken
            ])->withCookie($this->getRefreshCookie($newToken));

        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not refresh token'], 401);
        }
    }

    // LOGOUT
    public function logout(Request $request)
    {
        try {
            $token = JWTAuth::getToken();
            if ($token) {
                JWTAuth::invalidate($token);
            }
        } catch (\Exception $e) {
            // Token mungkin sudah tidak valid atau expired
        }

        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie(cookie('refresh_token', '', -1)); // hapus cookie
    }

    // COOKIE BUILDER
    protected function getRefreshCookie($token)
    {
        return cookie(
            'refresh_token',
            $token,
            60 * 24 * 7,   // 7 hari
            '/',
            config('session.domain', null), // atau null
            true,           // secure
            true,           // httpOnly
            false,          // raw
            'Strict'        // SameSite
        );
    }
}

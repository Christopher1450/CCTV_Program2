<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $refreshCookie = $this->getRefreshCookie($token);

        return response()->json([
            'token' => $token,
            'user' => Auth::user()
        ])->withCookie($refreshCookie);
    }

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

        public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            // Ignore invalid token
        }

        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie(cookie('refresh_token', '', -1)); // delete cookie
    }

    protected function getRefreshCookie($token)
    {
        return cookie(
            'refresh_token',
            $token,
            60 * 24 * 7,      // 7 days
            '/',
            null,
            true,             // secure
            true,             // httpOnly
            false,
            'Strict'          // SameSite policy
        );
    }
}

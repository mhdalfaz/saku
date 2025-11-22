<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiController
{
    /**
     * REGISTER
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        try {
            $token = JWTAuth::fromUser($user);
        } catch (JWTException $e) {
            return $this->error("Gagal membuat token", 500);
        }

        return $this->success([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], "Registrasi berhasil", 201);
    }

    /**
     * LOGIN
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return $this->error("Email atau password salah", 401);
            }
        } catch (JWTException $e) {
            return $this->error("Gagal membuat token", 500);
        }

        return $this->success([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ], "Login berhasil");
    }

    /**
     * GET PROFILE / CURRENT USER / ME
     */
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return $this->success($user, "Data profil");
        } catch (\Exception $e) {
            return $this->error("Token tidak valid atau expired", 401);
        }
    }

    /**
     * LOGOUT
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return $this->success(null, "Logout berhasil");
        } catch (JWTException $e) {
            return $this->error("Gagal logout, coba lagi", 500);
        }
    }

    /**
     * REFRESH TOKEN
     */
    public function refresh()
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return $this->success([
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60
            ], "Token refreshed");

        } catch (JWTException $e) {
            return $this->error("Token refresh gagal", 500);
        }
    }
}

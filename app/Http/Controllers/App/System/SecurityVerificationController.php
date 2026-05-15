<?php

namespace App\Http\Controllers\App\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SecurityVerificationController extends Controller
{
    /**
     * Mengecek apakah sesi verifikasi masih berlaku (30 menit).
     */
    public function check(Request $request): JsonResponse
    {
        $lastVerified = session('last_security_verification_at');
        $isValid = $lastVerified && now()->diffInMinutes($lastVerified) < 30;

        return response()->json([
            'is_valid' => $isValid,
            'expires_in' => $isValid ? 30 - now()->diffInMinutes($lastVerified) : 0
        ]);
    }

    /**
     * Memverifikasi PIN atau Password.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:pin,password,biometric',
            'value' => 'required_unless:type,biometric|string',
        ]);

        $user = $request->user();
        $isSuccess = false;

        if ($request->type === 'pin') {
            // Jika PIN belum diset, kita kasih tau user
            if (!$user->pin) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN belum diatur. Silakan gunakan Password utama atau atur PIN di profil.'
                ], 422);
            }
            $isSuccess = Hash::check($request->value, $user->pin);
        } elseif ($request->type === 'password') {
            $isSuccess = Hash::check($request->value, $user->password);
        } elseif ($request->type === 'biometric') {
            // Simulasi sukses biometric (WebAuthn implementation would be more complex)
            // Di sini kita asumsikan sensor di sisi client sudah ok
            $isSuccess = true; 
        }

        if ($isSuccess) {
            session(['last_security_verification_at' => now()]);
            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Verifikasi gagal. Silakan coba lagi.'
        ], 401);
    }

    /**
     * Mengatur PIN baru (Hanya untuk testing/initial setup gampang).
     */
    public function updatePin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, $request->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Password salah.'], 401);
        }

        $request->user()->update([
            'pin' => Hash::make($request->pin)
        ]);

        return response()->json(['success' => true, 'message' => 'PIN berhasil diperbarui.']);
    }
}

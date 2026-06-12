<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Memproses login secara Hybrid:
     * - Cek kredensial ke Database Lokal (Tabel users)
     * - Menyimpan Role di dalam Session untuk proteksi Middleware
     */
    public function login(Request $request)
    {
        // Validasi input dari form
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Coba login menggunakan Auth bawaan Laravel (Database Lokal 'sidagas')
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            
            // Ambil data user yang sedang login
            $user = Auth::user();

            // Simpan Session Role layaknya BFF, agar Middleware sebelumnya tetap jalan
            session([
                'token' => 'local-token-' . uniqid(), // Dummy token untuk menjaga integrasi jika dibutuhkan
                'role' => $user->role,
                'name' => $user->name
            ]);

            // Redirect ke halaman yang sesuai dengan role-nya
            return $this->redirectBasedOnRole($user->role);
        }

        // Jika gagal login
        return back()->withErrors([
            'email' => 'Email atau password salah. Cek kembali akun Anda.',
        ])->withInput();
    }

    /**
     * Logout dan hapus session
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->forget(['token', 'role', 'name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Helper untuk Routing Otomatis
     */
    private function redirectBasedOnRole($role)
    {
        switch ($role) {
            case 'admin':
                return redirect('/admin');
            case 'karyawan':
                return redirect('/karyawan');
            case 'driver':
                return redirect('/driver');
            case 'pelanggan':
                return redirect('/pelanggan');
            default:
                return redirect('/login');
        }
    }
}

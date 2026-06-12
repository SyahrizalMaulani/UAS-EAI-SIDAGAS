<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Cek token di session dan validasi array role yang diizinkan.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Verifikasi apakah Session memiliki "token" (Tanda sudah login)
        if (!$request->session()->has('token')) {
            return redirect('/login')->withErrors(['akses' => 'Anda harus login terlebih dahulu.']);
        }

        // 2. Ambil "role" pengguna dari session
        $userRole = $request->session()->get('role');

        // 3. Verifikasi Role (RBAC)
        // Cek apakah role dari session pengguna termasuk dalam daftar $roles yang diperbolehkan di route ini.
        if (!empty($roles) && !in_array($userRole, $roles)) {
            // Tolak akses (bisa redirect ke halaman 403 atau kembali ke beranda)
            abort(403, 'Akses Ditolak: Role Anda (' . strtoupper($userRole) . ') tidak diizinkan mengakses halaman ini.');
        }

        // 4. Lanjutkan request jika lolos pengecekan
        return $next($request);
    }
}

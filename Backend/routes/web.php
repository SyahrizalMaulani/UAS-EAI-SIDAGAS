<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routing untuk SIDAGAS Frontend dengan RBAC.
|
*/

// Halaman Utama
Route::get('/', function () {
    return view('home', ['title'=>'SIDAGAS']);
});

// Otentikasi (BFF ke API Gateway)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Role-Based Access Control (RBAC) Route Groups
|--------------------------------------------------------------------------
| Middleware 'RoleMiddleware' mem-filter akses berdasarkan role di session.
| Format: middleware([RoleMiddleware::class . ':role1,role2'])
*/

use App\Http\Controllers\AdminController;

// ==========================================
// 1. Panel Admin (Hanya untuk role 'admin')
// ==========================================
Route::middleware([RoleMiddleware::class . ':admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard']);
    Route::get('/orders', [AdminController::class, 'orders']);
    Route::get('/inventory', [AdminController::class, 'inventory']);
});


use App\Http\Controllers\KaryawanController;

// ==========================================
// 2. Panel Karyawan (Hanya untuk 'karyawan' atau 'admin')
// ==========================================
Route::middleware([RoleMiddleware::class . ':karyawan,admin'])->prefix('karyawan')->group(function () {
    Route::get('/', [KaryawanController::class, 'intake']);
    Route::get('/production', [KaryawanController::class, 'production']);
    Route::get('/ready', [KaryawanController::class, 'ready']);
});


use App\Http\Controllers\DriverController;

// ==========================================
// 3. Panel Driver (Hanya untuk 'driver')
// ==========================================
Route::middleware([RoleMiddleware::class . ':driver'])->prefix('driver')->group(function () {
    Route::get('/', [DriverController::class, 'deliveries']);
    Route::get('/active', [DriverController::class, 'active']);
    Route::get('/history', [DriverController::class, 'history']);
});


use App\Http\Controllers\PelangganController;

// ==========================================
// 4. Panel Pelanggan (Hanya untuk 'pelanggan')
// ==========================================
Route::middleware([RoleMiddleware::class . ':pelanggan'])->prefix('pelanggan')->group(function () {
    Route::get('/', [PelangganController::class, 'catalog']);
    Route::get('/checkout', [PelangganController::class, 'checkout']);
    Route::get('/tracking', [PelangganController::class, 'tracking']);
});


// Rute yang lama, saya kelompokkan di sini agar tidak hilang
Route::get('/dashboard', [CustomerController::class, 'index']);
Route::get('/TentangKami', function () {
    return view('Web.TentangKami', ['title'=>'Tentang Kami']);
});
Route::get('/Produk', function () {
    return view('Web.Produk', ['title'=>'Produk']);
});
Route::get('/Kontak', function () {
    return view('Web.Kontak', ['title'=>'Kontak']);
});
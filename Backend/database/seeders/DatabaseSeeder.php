<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Data Akun Admin
        User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@sidagas.com',
            'password' => bcrypt('admin'),
            'role' => 'admin'
        ]);

        // Data Akun Karyawan Pabrik
        User::factory()->create([
            'name' => 'Karyawan Pabrik',
            'email' => 'karyawan@sidagas.com',
            'password' => bcrypt('karyawan'),
            'role' => 'karyawan'
        ]);

        // Data Akun Driver
        User::factory()->create([
            'name' => 'Driver Pengiriman',
            'email' => 'driver@sidagas.com',
            'password' => bcrypt('driver'),
            'role' => 'driver'
        ]);

        // Data Akun Pelanggan
        User::factory()->create([
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@sidagas.com',
            'password' => bcrypt('pelanggan'),
            'role' => 'pelanggan'
        ]);
    }
}

<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Cek apakah admin sudah ada
        if (!User::where('nim', 'admin001')->exists()) {
            // Buat akun admin
            User::create([
                'name' => 'Admin IoT Lab',
                'nim' => 'admin001',
                'email' => 'admin@iot.lab',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => Carbon::now(),
                'last_login_at' => Carbon::now(),
            ]);
        }
    }
}

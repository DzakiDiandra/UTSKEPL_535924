<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
                'is_admin' => true,
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'pelanggan@example.com'],
            [
                'name' => 'Pelanggan Contoh',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        );
    }
}

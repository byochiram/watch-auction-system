<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'Superadmin'],
            [
                'name' => 'Superadmin Lelang',
                'email' => 'admin@lelangjam.local',
                'password' => Hash::make('password'),
                'role' => 'SUPERADMIN',
                'status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]
        );
    }
}
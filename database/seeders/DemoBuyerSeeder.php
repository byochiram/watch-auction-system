<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoBuyerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => env('DEMO_BUYER_EMAIL', 'demo@example.com'),
            ],
            [
                'name' => 'Demo Buyer',
                'username' => env('DEMO_BUYER_USERNAME', 'demo_buyer'),
                'email_verified_at' => now(),
                'password' => Hash::make(env('DEMO_BUYER_PASSWORD', 'Demo12345!')),
                'role' => 'BIDDER',
                'status' => 'ACTIVE',
                'suspended_until' => null,
                'suspend_reason' => null,
                'profile_photo_path' => null,
            ]
        );
    }
}
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
        // Admin
        User::create([
            'name' => 'Admin Khidma',
            'email' => 'admin@khidmanow.com',
            'phone' => '0500000001',
            'password' => Hash::make('password'),
            'type' => 'admin',
        ]);

        // Regular User
        User::create([
            'name' => 'John Doe',
            'email' => 'user@khidmanow.com',
            'phone' => '0500000002',
            'password' => Hash::make('password'),
            'type' => 'user',
        ]);

        // Service Provider
        User::create([
            'name' => 'Provider Smith',
            'email' => 'provider@khidmanow.com',
            'phone' => '0500000003',
            'password' => Hash::make('password'),
            'type' => 'service_provider',
        ]);
    }
}

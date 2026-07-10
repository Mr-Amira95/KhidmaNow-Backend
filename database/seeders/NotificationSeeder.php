<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (Notification::where('type', 'system')->exists()) {
            return;
        }

        User::query()->chunkById(50, function ($users) {
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Welcome to KhidmaNow',
                    'body' => 'Thanks for joining KhidmaNow! Explore trusted providers near you.',
                    'type' => 'system',
                    'is_read' => true,
                ]);
            }
        });

        $customers = User::where('user_type', 'customer')->get();
        foreach ($customers as $customer) {
            Notification::create([
                'user_id' => $customer->id,
                'title' => 'Ramadan Offer',
                'body' => 'Get 10% off your next home cleaning booking this month.',
                'type' => 'system',
                'is_read' => fake()->boolean(40),
            ]);
        }
    }
}

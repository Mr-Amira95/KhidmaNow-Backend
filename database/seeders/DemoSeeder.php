<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Seeds a full set of realistic demo content so the admin portal and
     * mobile/API clients have real data to browse and exercise.
     */
    public function run(): void
    {
        $this->call([
            LocationSeeder::class,
            CategorySeeder::class,
            SettingSeeder::class,
            UserSeeder::class,
            RolePermissionSeeder::class,
            ServiceRequestSeeder::class,
            QuotationSeeder::class,
            ChatSeeder::class,
            ChatbotSeeder::class,
            SupportContentSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}

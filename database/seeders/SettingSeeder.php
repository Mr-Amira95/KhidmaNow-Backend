<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'commission_rate', 'value' => '15', 'type' => 'number'],
            ['key' => 'support_email', 'value' => 'support@khidmanow.com', 'type' => 'string'],
            ['key' => 'support_phone', 'value' => '+966500000000', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
            ['key' => 'provider_rejection_limit', 'value' => '3', 'type' => 'number'],
            ['key' => 'provider_rejection_window_hours', 'value' => '24', 'type' => 'number'],
            ['key' => 'provider_suspension_duration_hours', 'value' => '72', 'type' => 'number'],
            ['key' => 'provider_debt_suspension_threshold', 'value' => '50', 'type' => 'number'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }
    }
}

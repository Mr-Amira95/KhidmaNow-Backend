<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        if (Setting::exists()) {
            return;
        }

        $settings = [
            ['key' => 'commission_rate', 'value' => '15', 'type' => 'number'],
            ['key' => 'support_email', 'value' => 'support@khidmanow.com', 'type' => 'string'],
            ['key' => 'support_phone', 'value' => '+966500000000', 'type' => 'string'],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}

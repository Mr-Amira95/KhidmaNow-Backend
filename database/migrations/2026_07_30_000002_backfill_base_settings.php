<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

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

        DB::table('settings')->insertOrIgnore(array_map(
            fn (array $setting) => [...$setting, 'created_at' => $now, 'updated_at' => $now],
            $settings
        ));
    }

    public function down(): void
    {
        // Base settings are relied upon elsewhere; do not remove on rollback.
    }
};

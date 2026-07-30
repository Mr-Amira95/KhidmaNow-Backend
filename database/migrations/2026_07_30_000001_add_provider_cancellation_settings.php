<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('settings')->insertOrIgnore([
            [
                'key'        => 'provider_cancellation_limit',
                'value'      => '3',
                'type'       => 'number',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'        => 'provider_cancellation_window_days',
                'value'      => '7',
                'type'       => 'number',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'provider_cancellation_limit',
            'provider_cancellation_window_days',
        ])->delete();
    }
};

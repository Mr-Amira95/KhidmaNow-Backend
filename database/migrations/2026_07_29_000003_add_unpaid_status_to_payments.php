<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('unpaid', 'pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash', 'cliq') NULL");
    }

    public function down(): void
    {
        DB::statement("UPDATE payments SET status = 'pending' WHERE status = 'unpaid'");
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash', 'cliq') NOT NULL");
    }
};

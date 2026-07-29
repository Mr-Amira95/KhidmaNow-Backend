<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE service_requests MODIFY payment_status ENUM('unpaid', 'pending', 'paid') NOT NULL DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::statement("UPDATE service_requests SET payment_status = 'unpaid' WHERE payment_status = 'pending'");
        DB::statement("ALTER TABLE service_requests MODIFY payment_status ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid'");
    }
};

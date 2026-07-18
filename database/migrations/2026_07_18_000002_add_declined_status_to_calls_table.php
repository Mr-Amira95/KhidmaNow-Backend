<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE calls MODIFY status ENUM('ringing', 'ongoing', 'ended', 'declined') NOT NULL DEFAULT 'ringing'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE calls MODIFY status ENUM('ringing', 'ongoing', 'ended') NOT NULL DEFAULT 'ringing'");
    }
};

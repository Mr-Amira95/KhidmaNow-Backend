<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE calls MODIFY status ENUM('ringing', 'ongoing', 'ended') NOT NULL DEFAULT 'ringing'");

        Schema::table('calls', function (Blueprint $table) {
            $table->timestamp('accepted_at')->nullable()->after('started_at');
        });
    }

    public function down(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->dropColumn('accepted_at');
        });

        DB::statement("ALTER TABLE calls MODIFY status ENUM('ongoing', 'ended') NOT NULL DEFAULT 'ongoing'");
    }
};

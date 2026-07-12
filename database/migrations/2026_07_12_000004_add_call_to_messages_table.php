<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY COLUMN media_type ENUM('text', 'image', 'audio', 'video', 'file', 'call') NOT NULL DEFAULT 'text'");

        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('call_id')->nullable()->after('sender_id')->constrained('calls')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('call_id');
        });

        DB::statement("ALTER TABLE messages MODIFY COLUMN media_type ENUM('text', 'image', 'audio', 'video', 'file') NOT NULL DEFAULT 'text'");
    }
};

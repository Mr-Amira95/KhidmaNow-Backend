<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->timestamp('deleted_by_user_at')->nullable()->after('last_message_at');
            $table->timestamp('deleted_by_provider_at')->nullable()->after('deleted_by_user_at');
            $table->unique(['user_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_rooms', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'provider_id']);
            $table->dropColumn(['deleted_by_user_at', 'deleted_by_provider_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_rooms', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            $table->string('session_id')->nullable()->after('user_id');
            $table->string('direction')->nullable()->after('session_id');
            $table->index('session_id');
        });

        Schema::table('chatbot_rooms', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_rooms', function (Blueprint $table) {
            $table->dropIndex(['session_id']);
            $table->dropColumn(['session_id', 'direction']);
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
        });

        Schema::table('chatbot_rooms', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};

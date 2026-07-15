<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->string('direction')->nullable()->after('role');
            $table->foreignId('quotation_id')->nullable()->after('direction')->constrained('quotations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_messages', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropColumn(['direction', 'quotation_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'confirmed', 'cancelled'])
                ->default('pending')
                ->change();
        });

        Schema::table('service_request_track', function (Blueprint $table) {
            $table->enum('from_status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'confirmed', 'cancelled'])
                ->nullable()
                ->change();
            $table->enum('to_status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'confirmed', 'cancelled'])
                ->change();
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('sub_category_id')->constrained('quotations')->nullOnDelete();
            $table->foreignId('chat_room_id')->nullable()->after('quotation_id')->constrained('chat_rooms')->nullOnDelete();
            $table->enum('source', ['direct', 'quotation', 'chat'])->default('direct')->after('chat_room_id');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quotation_id');
            $table->dropConstrainedForeignId('chat_room_id');
            $table->dropColumn('source');
        });

        Schema::table('service_request_track', function (Blueprint $table) {
            $table->enum('from_status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])->nullable()->change();
            $table->enum('to_status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])->change();
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
        });
    }
};

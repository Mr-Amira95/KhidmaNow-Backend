<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_request_track', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained('service_requests')->cascadeOnDelete();
            $table->enum('from_status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled'])->nullable();
            $table->enum('to_status', ['pending', 'approved', 'in_progress', 'completed', 'cancelled']);
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('date_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_track');
    }
};

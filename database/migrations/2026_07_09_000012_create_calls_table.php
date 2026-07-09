<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chat_rooms')->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users');
            $table->enum('call_type', ['audio', 'video']);
            $table->string('agora_channel');
            $table->enum('status', ['ongoing', 'ended'])->default('ongoing');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};

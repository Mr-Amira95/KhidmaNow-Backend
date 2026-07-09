<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_track', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $table->enum('from_status', ['open', 'closed', 'cancelled'])->nullable();
            $table->enum('to_status', ['open', 'closed', 'cancelled']);
            $table->foreignId('changed_by')->constrained('users');
            $table->timestamp('date_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_track');
    }
};

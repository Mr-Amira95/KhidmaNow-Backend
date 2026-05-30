<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favourites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('favourite_item_id');
            $table->enum('favourite_type', ['provider', 'category']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favourites');
    }
};

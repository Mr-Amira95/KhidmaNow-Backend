<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_cliq_details', function (Blueprint $table) {
            $table->id();
            $table->string('alias');
            $table->string('bank_name');
            $table->string('holder_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_cliq_details');
    }
};

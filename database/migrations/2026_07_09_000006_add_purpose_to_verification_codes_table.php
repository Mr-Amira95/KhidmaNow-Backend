<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->enum('purpose', ['register', 'reset_password'])->default('reset_password')->after('identifier');
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropUnique('verification_codes_identifier_unique');
            $table->unique(['identifier', 'purpose']);
        });
    }

    public function down(): void
    {
        Schema::table('verification_codes', function (Blueprint $table) {
            $table->dropUnique(['identifier', 'purpose']);
        });

        Schema::table('verification_codes', function (Blueprint $table) {
            $table->unique('identifier');
            $table->dropColumn('purpose');
        });
    }
};

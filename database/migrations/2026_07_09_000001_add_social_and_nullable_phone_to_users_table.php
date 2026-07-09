<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('apple_id')->nullable()->unique()->after('google_id');
        });

        // Raw ALTER to avoid the doctrine/dbal dependency required by Schema::change().
        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(255) NULL');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'apple_id']);
        });

        DB::statement('ALTER TABLE users MODIFY phone VARCHAR(255) NOT NULL');
    }
};

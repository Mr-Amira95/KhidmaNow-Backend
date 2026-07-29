<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $firstId = DB::table('company_cliq_details')->orderBy('id')->value('id');

        if ($firstId) {
            DB::table('company_cliq_details')->where('id', '!=', $firstId)->delete();
        }

        Schema::table('company_cliq_details', function (Blueprint $table) {
            $table->unsignedTinyInteger('lock_key')->default(1)->after('id');
            $table->unique('lock_key');
        });
    }

    public function down(): void
    {
        Schema::table('company_cliq_details', function (Blueprint $table) {
            $table->dropUnique(['company_cliq_details_lock_key_unique']);
            $table->dropColumn('lock_key');
        });
    }
};

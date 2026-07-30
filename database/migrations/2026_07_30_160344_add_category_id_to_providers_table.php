<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('city_id')->constrained('categories')->nullOnDelete();
        });

        // Backfill existing providers from the category of their first selected sub-category,
        // so previously-registered providers aren't left without a commission-rate source.
        $rows = DB::table('provider_sub_categories')
            ->join('sub_categories', 'sub_categories.id', '=', 'provider_sub_categories.sub_category_id')
            ->orderBy('provider_sub_categories.id')
            ->select('provider_sub_categories.provider_id', 'sub_categories.category_id')
            ->get()
            ->unique('provider_id');

        foreach ($rows as $row) {
            DB::table('providers')->where('id', $row->provider_id)->update(['category_id' => $row->category_id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};

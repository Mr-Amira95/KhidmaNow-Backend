<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name_ar')->after('id');
            $table->string('name_en')->after('name_ar');
            $table->text('description_ar')->after('name_en');
            $table->text('description_en')->after('description_ar');
            $table->boolean('is_active')->default(true)->after('icon');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->string('name_ar')->after('category_id');
            $table->string('name_en')->after('name_ar');
            $table->text('description_ar')->after('name_en');
            $table->text('description_en')->after('description_ar');
            $table->boolean('is_active')->default(true)->after('icon');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn(['name', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('description')->after('name');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en', 'is_active']);
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->string('name')->after('category_id');
            $table->string('description')->after('name');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en', 'description_ar', 'description_en', 'is_active']);
        });
    }
};

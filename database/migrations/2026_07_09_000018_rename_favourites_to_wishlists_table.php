<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('favourites', 'wishlists');

        Schema::table('wishlists', function (Blueprint $table) {
            $table->renameColumn('favourite_item_id', 'item_id');
            $table->renameColumn('favourite_type', 'item_type');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->enum('item_type', ['category', 'sub_category', 'provider'])->change();
            $table->unique(['user_id', 'item_type', 'item_id'], 'wishlists_user_item_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wishlists', function (Blueprint $table) {
            $table->dropUnique('wishlists_user_item_unique');
            $table->renameColumn('item_id', 'favourite_item_id');
            $table->renameColumn('item_type', 'favourite_type');
        });

        Schema::table('wishlists', function (Blueprint $table) {
            $table->enum('favourite_type', ['provider', 'category'])->change();
        });

        Schema::rename('wishlists', 'favourites');
    }
};

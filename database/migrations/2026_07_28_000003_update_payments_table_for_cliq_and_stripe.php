<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash', 'cliq') NOT NULL");

        Schema::table('payments', function (Blueprint $table) {
            $table->string('receipt_path')->nullable()->after('transaction_ref');
            $table->string('rejection_reason')->nullable()->after('receipt_path');
            $table->string('stripe_payment_intent_id')->nullable()->after('rejection_reason');
            $table->string('stripe_client_secret')->nullable()->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['receipt_path', 'rejection_reason', 'stripe_payment_intent_id', 'stripe_client_secret']);
        });

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('card', 'cash') NOT NULL");
    }
};

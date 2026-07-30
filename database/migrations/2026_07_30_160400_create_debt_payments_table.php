<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debt_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['card', 'cash', 'cliq'])->nullable();
            $table->enum('status', ['unpaid', 'pending', 'paid', 'failed'])->default('unpaid');
            $table->string('transaction_ref')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_client_secret')->nullable();
            $table->string('stripe_checkout_session_id')->nullable();
            $table->text('stripe_checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debt_payments');
    }
};

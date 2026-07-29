<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $unpaidRequests = DB::table('service_requests')
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('payments')
                    ->whereColumn('payments.service_request_id', 'service_requests.id');
            })
            ->get(['id', 'user_id', 'price', 'created_at', 'updated_at']);

        foreach ($unpaidRequests as $serviceRequest) {
            DB::table('payments')->insert([
                'user_id'            => $serviceRequest->user_id,
                'service_request_id' => $serviceRequest->id,
                'amount'             => $serviceRequest->price ?? 0,
                'payment_method'     => null,
                'status'             => 'unpaid',
                'created_at'         => $serviceRequest->created_at,
                'updated_at'         => $serviceRequest->updated_at,
            ]);
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'pending', 'paid'])->default('unpaid')->after('status');
        });

        DB::statement("
            UPDATE service_requests sr
            LEFT JOIN payments p ON p.id = (
                SELECT id FROM payments WHERE payments.service_request_id = sr.id ORDER BY id DESC LIMIT 1
            )
            SET sr.payment_status = CASE
                WHEN p.status = 'paid' THEN 'paid'
                WHEN p.status = 'pending' THEN 'pending'
                ELSE 'unpaid'
            END
        ");
    }
};

@extends('admin.layouts.app')

@section('title', 'Payments')
@section('page', 'payments')

@section('content')
    <div id="payments-banner" class="hidden mb-4" role="alert"></div>

    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <select id="payments-method-filter" class="input-field-sm">
                    <option value="">All methods</option>
                    <option value="cash">Cash</option>
                    <option value="cliq">CliQ</option>
                    <option value="card">Card</option>
                </select>
                <select id="payments-status-filter" class="input-field-sm">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Customer</th>
                        <th class="py-3 px-4">Service Request</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="payments-table-body"></tbody>
            </table>
        </div>

        <div id="payments-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="payment-detail-modal" title="Payment details">
        <div id="payment-detail-body"></div>
    </x-admin.modal>

    <x-admin.modal id="payment-reject-modal" title="Reject CliQ payment">
        <form id="payment-reject-form" class="space-y-4" novalidate>
            <x-admin.textarea-field
                name="rejection_reason"
                label="Reason for rejection"
                placeholder="e.g. Transfer amount didn't match, receipt unreadable, no transfer found..."
            />
            <x-admin.submit-button data-permission="payments.edit">Reject payment</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection

@extends('admin.layouts.app')

@section('title', 'Debt Payments')
@section('page', 'debt-payments')

@section('content')
    <div id="debt-payments-banner" class="hidden mb-4" role="alert"></div>

    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <select id="debt-payments-method-filter" class="input-field-sm">
                    <option value="">All methods</option>
                    <option value="cash">Cash</option>
                    <option value="cliq">CliQ</option>
                    <option value="card">Card</option>
                </select>
                <select id="debt-payments-status-filter" class="input-field-sm">
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
                        <th class="py-3 px-4">Provider</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4">Method</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="debt-payments-table-body"></tbody>
            </table>
        </div>

        <div id="debt-payments-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="debt-payment-detail-modal" title="Debt payment details">
        <div id="debt-payment-detail-body"></div>
    </x-admin.modal>

    <x-admin.modal id="debt-payment-reject-modal" title="Reject debt payment">
        <form id="debt-payment-reject-form" class="space-y-4" novalidate>
            <x-admin.textarea-field
                name="rejection_reason"
                label="Reason for rejection"
                placeholder="e.g. Transfer amount didn't match, receipt unreadable, no transfer found..."
            />
            <x-admin.submit-button data-permission="payments.edit">Reject payment</x-admin.submit-button>
        </form>
    </x-admin.modal>
@endsection

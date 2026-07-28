@extends('admin.layouts.app')

@section('title', 'Service Providers')
@section('page', 'users-providers')

@section('content')
    <div data-permission="settings.edit" class="card-surface mb-4 p-4">
        <p class="mb-3 text-sm font-semibold">Suspension policy</p>
        <p class="mb-3 text-xs text-zinc-400">
            A provider who rejects this many requests within the time window is automatically suspended for the configured duration.
            A provider whose unpaid cash-job commission reaches the debt threshold is suspended until an admin records their payment.
        </p>
        <div class="flex flex-wrap items-end gap-3">
            <label class="text-xs text-zinc-500">
                Rejection limit
                <input id="policy-rejection-limit" type="number" min="1" class="input-field-sm mt-1 block w-32">
            </label>
            <label class="text-xs text-zinc-500">
                Window (hours)
                <input id="policy-window-hours" type="number" min="1" class="input-field-sm mt-1 block w-32">
            </label>
            <label class="text-xs text-zinc-500">
                Suspension duration (hours)
                <input id="policy-suspension-hours" type="number" min="1" class="input-field-sm mt-1 block w-32">
            </label>
            <label class="text-xs text-zinc-500">
                Debt suspension threshold
                <input id="policy-debt-threshold" type="number" min="0" step="0.01" class="input-field-sm mt-1 block w-32">
            </label>
            <button id="policy-save-button" type="button" class="btn btn-primary">Save Policy</button>
            <span id="policy-save-status" class="text-xs text-zinc-400"></span>
        </div>
    </div>

    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input id="providers-search" type="text" placeholder="Search business name, phone..."
                    class="w-64 input-field-sm">
                <select id="providers-verified-filter" class="input-field-sm">
                    <option value="">All providers</option>
                    <option value="1">Verified</option>
                    <option value="0">Pending</option>
                </select>
                <select id="providers-suspended-filter" class="input-field-sm">
                    <option value="">All statuses</option>
                    <option value="0">Active</option>
                    <option value="1">Suspended</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Business</th>
                        <th class="py-3 px-4">Owner</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4">Verification</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Owed</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="providers-table-body"></tbody>
            </table>
        </div>

        <div id="providers-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="provider-detail-modal" title="Provider details">
        <div id="provider-detail-body"></div>
    </x-admin.modal>
@endsection

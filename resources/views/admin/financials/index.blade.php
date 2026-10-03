@extends('admin.layouts.app')

@section('title', 'Financials')
@section('page', 'financials')

@section('content')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    <div id="financials-banner" class="hidden mb-4" role="alert"></div>

    <div class="card-surface">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input type="date" id="financials-from-filter" class="input-field-sm" aria-label="From date">
                <span class="text-sm text-zinc-400">to</span>
                <input type="date" id="financials-to-filter" class="input-field-sm" aria-label="To date">
                <button type="button" id="financials-apply-range" class="btn btn-secondary px-3 py-1.5">Apply</button>
                <button type="button" id="financials-clear-range" class="btn btn-secondary px-3 py-1.5">All time</button>
            </div>
        </div>
    </div>

    <div class="stagger mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Income</p>
            <p id="stat-income" class="mt-1 text-2xl font-semibold text-brand-green-700 dark:text-brand-green-400">—</p>
            <p class="mt-1 text-xs text-zinc-400">Client payments + provider debt repayments</p>
        </div>
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Outcome</p>
            <p id="stat-outcome" class="mt-1 text-2xl font-semibold text-rose-600 dark:text-rose-400">—</p>
            <p class="mt-1 text-xs text-zinc-400">Payouts paid to providers</p>
        </div>
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Net</p>
            <p id="stat-net" class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-50">—</p>
            <p class="mt-1 text-xs text-zinc-400">Income minus outcome for this range</p>
        </div>
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Commission earned</p>
            <p id="stat-commission" class="mt-1 text-2xl font-semibold text-accent-700 dark:text-accent-400">—</p>
            <p class="mt-1 text-xs text-zinc-400">Actual company revenue from completed jobs</p>
        </div>
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Pending payouts</p>
            <p id="stat-pending-payouts" class="mt-1 text-2xl font-semibold text-brand-orange-600 dark:text-brand-orange-400">—</p>
            <p class="mt-1 text-xs text-zinc-400">Owed to providers, not yet paid out (current)</p>
        </div>
        <div class="card-surface p-5">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Outstanding debt</p>
            <p id="stat-outstanding-debt" class="mt-1 text-2xl font-semibold text-rose-600 dark:text-rose-400">—</p>
            <p class="mt-1 text-xs text-zinc-400">Owed to the company by providers (current)</p>
        </div>
    </div>

    <div class="card-surface mt-4 p-5">
        <p class="mb-4 text-sm font-semibold text-zinc-900 dark:text-zinc-50">Monthly trend</p>
        <div class="h-72">
            <canvas id="financials-chart"></canvas>
        </div>
    </div>

    <div class="card-surface mt-4">
        <div class="card-header">
            <div class="flex flex-wrap items-center gap-2">
                <input type="text" id="financials-provider-search" class="input-field-sm" placeholder="Search providers...">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
                        <th class="py-3 px-4">Provider</th>
                        <th class="py-3 px-4">Paid payouts</th>
                        <th class="py-3 px-4">Pending payouts</th>
                        <th class="py-3 px-4">Paid debt</th>
                        <th class="py-3 px-4">Wallet balance</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="financials-providers-table-body"></tbody>
            </table>
        </div>

        <div id="financials-providers-pagination" class="flex items-center justify-between gap-3 p-4"></div>
    </div>

    <x-admin.modal id="financials-provider-modal" title="Provider financials">
        <div id="financials-provider-body"></div>
    </x-admin.modal>
@endsection

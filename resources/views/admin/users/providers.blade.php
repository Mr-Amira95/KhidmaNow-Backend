@extends('admin.layouts.app')

@section('title', 'Service Providers')
@section('page', 'users-providers')

@section('content')
    <div class="rounded-2xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
            <div class="flex flex-wrap items-center gap-2">
                <input id="providers-search" type="text" placeholder="Search business name, phone..."
                    class="w-64 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <select id="providers-verified-filter" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                    <option value="">All providers</option>
                    <option value="1">Verified</option>
                    <option value="0">Pending</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200/70 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                        <th class="py-3 px-4">Business</th>
                        <th class="py-3 px-4">Owner</th>
                        <th class="py-3 px-4">City</th>
                        <th class="py-3 px-4">Availability</th>
                        <th class="py-3 px-4">Verification</th>
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

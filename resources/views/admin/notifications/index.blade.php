@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page', 'notifications-index')

@section('content')
    <div class="rounded-2xl border border-zinc-200/70 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
            <input id="notifications-user-id-filter" type="number" placeholder="Filter by User ID..."
                class="w-48 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">

            <select id="notifications-action-filter"
                class="w-48 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="">All actions</option>
                <option value="service_request">Service Request</option>
                <option value="payment">Payment</option>
                <option value="chat">Chat</option>
                <option value="system">System</option>
            </select>

            <select id="notifications-read-filter"
                class="w-40 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-950">
                <option value="">All statuses</option>
                <option value="1">Read</option>
                <option value="0">Unread</option>
            </select>

            <a href="/admin/notifications/send" class="ml-auto rounded-lg bg-accent-600 px-4 py-2 text-sm font-semibold text-white hover:bg-accent-700">
                <i class="ph ph-plus"></i> Send Notification
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-zinc-200/70 text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                        <th class="py-3 px-4">Icon</th>
                        <th class="py-3 px-4">Title</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Action</th>
                        <th class="py-3 px-4">Recipient</th>
                        <th class="py-3 px-4">Sent At</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody id="notifications-table-body"></tbody>
            </table>
        </div>

        <div id="notifications-pagination" class="flex items-center justify-between border-t border-zinc-200/70 p-4 dark:border-zinc-800"></div>
    </div>
@endsection

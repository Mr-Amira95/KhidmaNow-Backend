@extends('admin.layouts.app')

@section('title', 'Notifications')
@section('page', 'notifications-index')

@section('content')
    <div class="card-surface">
        <div class="flex flex-wrap items-center gap-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
            <input id="notifications-user-id-filter" type="number" placeholder="Filter by User ID..."
                class="w-48 input-field-sm">

            <select id="notifications-action-filter"
                class="w-48 input-field-sm">
                <option value="">All actions</option>
                <option value="service_request">Service Request</option>
                <option value="payment">Payment</option>
                <option value="chat">Chat</option>
                <option value="system">System</option>
            </select>

            <select id="notifications-read-filter"
                class="w-40 input-field-sm">
                <option value="">All statuses</option>
                <option value="1">Read</option>
                <option value="0">Unread</option>
            </select>

            <a href="/admin/notifications/send" class="ml-auto btn btn-primary">
                <i class="ph ph-plus"></i> Send Notification
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="table-head-row">
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

@extends('admin.layouts.app')

@section('title', 'Support Tickets')
@section('page', 'support-tickets')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="flex flex-col card-surface lg:col-span-2">
            <div class="space-y-2 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <input id="tickets-search" type="text" placeholder="Search by subject, name, or phone..."
                    class="w-full input-field-sm">
                <select id="tickets-status-filter"
                    class="w-full input-field-sm">
                    <option value="">All statuses</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div id="tickets-list" class="max-h-[65vh] flex-1 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800"></div>
            <div id="tickets-pagination" class="flex items-center justify-between border-t border-zinc-200/70 p-4 dark:border-zinc-800"></div>
        </div>

        <div class="flex min-h-[70vh] flex-col card-surface lg:col-span-3">
            <div id="ticket-thread-header" class="flex items-center justify-between border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Select a ticket to view its conversation.</p>
            </div>
            <div id="ticket-thread-messages" class="flex-1 space-y-3 overflow-y-auto p-4"></div>
            <div id="ticket-thread-composer" class="border-t border-zinc-200/70 p-4 dark:border-zinc-800"></div>
        </div>
    </div>
@endsection

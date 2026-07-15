@extends('admin.layouts.app')

@section('title', 'Chatbot')
@section('page', 'chatbot')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="flex flex-col card-surface lg:col-span-2">
            <div class="space-y-3 border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <input id="chatbot-search" type="text" placeholder="Search by user name, phone, or session id..."
                    class="w-full input-field-sm">
                <select id="chatbot-direction-filter" class="w-full input-field-sm">
                    <option value="">All directions</option>
                    <option value="general">General</option>
                    <option value="rfq">RFQ</option>
                    <option value="providers">Providers</option>
                </select>
            </div>
            <div id="chatbot-list" class="max-h-[65vh] flex-1 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800"></div>
            <div id="chatbot-pagination" class="flex items-center justify-between border-t border-zinc-200/70 p-4 dark:border-zinc-800"></div>
        </div>

        <div class="flex min-h-[70vh] flex-col card-surface lg:col-span-3">
            <div id="chatbot-thread-header" class="border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Select a conversation to view its messages.</p>
            </div>
            <div id="chatbot-thread-messages" class="flex-1 space-y-3 overflow-y-auto p-4"></div>
        </div>
    </div>
@endsection

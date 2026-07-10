@extends('admin.layouts.app')

@section('title', 'Chats')
@section('page', 'chats')

@section('content')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="flex flex-col card-surface lg:col-span-2">
            <div class="border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <input id="chats-search" type="text" placeholder="Search by client, provider, or phone..."
                    class="w-full input-field-sm">
            </div>
            <div id="chats-list" class="max-h-[65vh] flex-1 divide-y divide-zinc-100 overflow-y-auto dark:divide-zinc-800"></div>
            <div id="chats-pagination" class="flex items-center justify-between border-t border-zinc-200/70 p-4 dark:border-zinc-800"></div>
        </div>

        <div class="flex min-h-[70vh] flex-col card-surface lg:col-span-3">
            <div id="chat-thread-header" class="border-b border-zinc-200/70 p-4 dark:border-zinc-800">
                <p class="text-sm text-zinc-500 dark:text-zinc-400">Select a chat to view its messages.</p>
            </div>
            <div id="chat-thread-messages" class="flex-1 space-y-3 overflow-y-auto p-4"></div>
        </div>
    </div>
@endsection

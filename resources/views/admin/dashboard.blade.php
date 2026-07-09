@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@section('content')
    <div class="flex flex-col items-center justify-center gap-3 rounded-2xl border border-zinc-200/70 bg-white p-12 text-center shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
        <p class="text-lg font-semibold">Welcome, <span id="dashboard-user-name">Admin</span></p>
        <p class="max-w-sm text-sm text-zinc-500 dark:text-zinc-400">
            Use the sidebar to manage clients, service providers, categories, locations, and app content.
        </p>
    </div>
@endsection

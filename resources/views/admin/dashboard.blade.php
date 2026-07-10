@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@section('content')
    <div class="card-surface relative overflow-hidden p-8">
        <div class="pointer-events-none absolute inset-0 dot-grid" aria-hidden="true"></div>
        <div class="relative flex flex-col items-start gap-2">
            <span class="inline-flex items-center gap-1.5 rounded-md bg-accent-50 px-2.5 py-1 text-xs font-medium text-accent-700 dark:bg-accent-950/40 dark:text-accent-400">
                <span class="h-1.5 w-1.5 rounded-full bg-accent-600"></span>
                {{ now()->format('l, j F Y') }}
            </span>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                Welcome back, <span id="dashboard-user-name">Admin</span>
            </h1>
            <p class="max-w-lg text-sm text-zinc-500 dark:text-zinc-400">
                Manage clients, service providers, categories, locations, and app content from one place.
            </p>
        </div>
    </div>

    <div class="stagger mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="/admin/users/clients" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-700 transition-transform duration-200 group-hover:scale-110 dark:bg-accent-950/40 dark:text-accent-400">
                <i class="ph ph-users text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Clients</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Browse, block, or review customer accounts and wishlists.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>

        <a href="/admin/users/providers" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-700 transition-transform duration-200 group-hover:scale-110 dark:bg-accent-950/40 dark:text-accent-400">
                <i class="ph ph-briefcase text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Service providers</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Verify documents and manage provider profiles.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>

        <a href="/admin/categories" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-orange-50 text-brand-orange-600 transition-transform duration-200 group-hover:scale-110 dark:bg-brand-orange-900/20">
                <i class="ph ph-squares-four text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Categories</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Organize the service catalog and sub-categories.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>

        <a href="/admin/chats" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-green-50 text-brand-green-900 transition-transform duration-200 group-hover:scale-110 dark:bg-brand-green-900/20 dark:text-brand-green-400">
                <i class="ph ph-chats-circle text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Chats</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Review conversations between clients and providers.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>

        <a href="/admin/support-tickets" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-50 text-accent-700 transition-transform duration-200 group-hover:scale-110 dark:bg-accent-950/40 dark:text-accent-400">
                <i class="ph ph-lifebuoy text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Support tickets</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Respond to open tickets and track resolutions.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>

        <a href="/admin/notifications/send" class="spotlight card-surface card-surface-hover group flex items-start gap-4 p-5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-orange-50 text-brand-orange-600 transition-transform duration-200 group-hover:scale-110 dark:bg-brand-orange-900/20">
                <i class="ph ph-paper-plane-tilt text-xl"></i>
            </span>
            <div>
                <p class="font-semibold text-zinc-900 dark:text-zinc-50">Send notification</p>
                <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Broadcast an announcement to clients or providers.</p>
            </div>
            <i class="ph ph-arrow-up-right ml-auto text-zinc-300 transition-all duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5 group-hover:text-accent-600 dark:group-hover:text-accent-400"></i>
        </a>
    </div>
@endsection

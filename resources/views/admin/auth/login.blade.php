@extends('admin.layouts.auth')

@section('title', 'Sign in')
@section('page', 'login')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">Sign in</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Use your admin email or phone number to continue.</p>
    </div>

    <form id="login-form" class="space-y-5" novalidate>
        <x-admin.text-field
            name="login"
            label="Email or phone"
            icon="ph-envelope-simple"
            placeholder="you@khidmanow.com"
            autocomplete="username"
            required
        />

        <x-admin.password-field name="password" label="Password" autocomplete="current-password" required>
            <a href="/admin/forgot-password" class="text-sm font-medium text-accent-600 hover:text-accent-700 dark:text-accent-400 dark:hover:text-accent-300">
                Forgot password?
            </a>
        </x-admin.password-field>

        <x-admin.submit-button busyLabel="Signing in">Sign in</x-admin.submit-button>
    </form>
@endsection

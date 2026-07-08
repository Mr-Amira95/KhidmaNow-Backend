@extends('admin.layouts.auth')

@section('title', 'Reset password')
@section('page', 'reset-password')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">Set a new password</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Choose a strong password with at least 8 characters.</p>
    </div>

    <form id="reset-password-form" class="space-y-5" novalidate>
        <x-admin.password-field
            name="password"
            label="New password"
            autocomplete="new-password"
            required
        />

        <x-admin.password-field
            name="password_confirmation"
            label="Confirm new password"
            autocomplete="new-password"
            required
        />

        <x-admin.submit-button busyLabel="Resetting">Reset password</x-admin.submit-button>
    </form>

    <a href="/admin/login" class="mt-6 flex items-center justify-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
        <i class="ph ph-arrow-left text-base"></i>
        Back to sign in
    </a>
@endsection

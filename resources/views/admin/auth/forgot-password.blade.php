@extends('admin.layouts.auth')

@section('title', 'Forgot password')
@section('page', 'forgot-password')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">Forgot your password?</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Enter the email or phone number on your admin account and we will send you a verification code.
        </p>
    </div>

    <form id="forgot-password-form" class="space-y-5" novalidate>
        <x-admin.text-field
            name="login"
            label="Email or phone"
            icon="ph-envelope-simple"
            placeholder="you@khidmanow.com"
            autocomplete="username"
            required
        />

        <x-admin.submit-button busyLabel="Sending code">Send code</x-admin.submit-button>
    </form>

    <a href="/admin/login" class="mt-6 flex items-center justify-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
        <i class="ph ph-arrow-left text-base"></i>
        Back to sign in
    </a>
@endsection

@extends('admin.layouts.auth')

@section('title', 'Verify code')
@section('page', 'verify-code')

@section('content')
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-50">Enter verification code</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            We sent a 4-digit code to <span id="masked-login" class="font-medium text-zinc-700 dark:text-zinc-300"></span>.
        </p>
    </div>

    <form id="verify-code-form" class="space-y-6" novalidate>
        <div>
            <div class="flex justify-between gap-3">
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 1"
                    class="otp-box h-14 w-full rounded-lg border border-zinc-300 bg-white text-center text-xl font-semibold text-zinc-900 transition-all duration-150 hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-accent-500/60 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 2"
                    class="otp-box h-14 w-full rounded-lg border border-zinc-300 bg-white text-center text-xl font-semibold text-zinc-900 transition-all duration-150 hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-accent-500/60 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 3"
                    class="otp-box h-14 w-full rounded-lg border border-zinc-300 bg-white text-center text-xl font-semibold text-zinc-900 transition-all duration-150 hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-accent-500/60 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 4"
                    class="otp-box h-14 w-full rounded-lg border border-zinc-300 bg-white text-center text-xl font-semibold text-zinc-900 transition-all duration-150 hover:border-zinc-400 focus:outline-none focus:ring-2 focus:ring-accent-500/60 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
            </div>
        </div>

        <x-admin.submit-button busyLabel="Verifying">Verify code</x-admin.submit-button>
    </form>

    <div class="mt-6 flex items-center justify-between text-sm">
        <a href="/admin/forgot-password" class="group flex items-center gap-1.5 font-medium text-zinc-500 transition-colors duration-150 hover:text-accent-600 dark:text-zinc-400 dark:hover:text-accent-400">
            <i class="ph ph-arrow-left text-base transition-transform duration-150 group-hover:-translate-x-0.5"></i>
            Back
        </a>
        <button id="resend-code" type="button" class="link-action disabled:cursor-not-allowed disabled:text-zinc-400 disabled:after:content-none dark:disabled:text-zinc-600">
            Resend code
        </button>
    </div>
@endsection

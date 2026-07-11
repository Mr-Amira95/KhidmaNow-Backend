@extends('admin.layouts.auth')

@section('title', __('auth.verify_code.title'))
@section('page', 'verify-code')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ __('auth.verify_code.heading') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
            {!! __('auth.verify_code.subheading', ['login' => '<span id="masked-login" class="font-medium text-zinc-700 dark:text-zinc-300"></span>']) !!}
        </p>
    </div>

    <form id="verify-code-form" class="space-y-6" novalidate>
        <div>
            {{-- Verification codes stay left-to-right even in RTL locales, matching how phone/PIN entry reads everywhere. --}}
            <div class="flex justify-between gap-3" dir="ltr">
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 1"
                    class="otp-box h-14 w-full rounded-xl border border-zinc-300 bg-white/90 text-center text-xl font-semibold text-zinc-900 transition-all duration-200 ease-out hover:border-zinc-400 focus:outline-none focus:ring-4 focus:ring-accent-500/15 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 2"
                    class="otp-box h-14 w-full rounded-xl border border-zinc-300 bg-white/90 text-center text-xl font-semibold text-zinc-900 transition-all duration-200 ease-out hover:border-zinc-400 focus:outline-none focus:ring-4 focus:ring-accent-500/15 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 3"
                    class="otp-box h-14 w-full rounded-xl border border-zinc-300 bg-white/90 text-center text-xl font-semibold text-zinc-900 transition-all duration-200 ease-out hover:border-zinc-400 focus:outline-none focus:ring-4 focus:ring-accent-500/15 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    aria-label="Digit 4"
                    class="otp-box h-14 w-full rounded-xl border border-zinc-300 bg-white/90 text-center text-xl font-semibold text-zinc-900 transition-all duration-200 ease-out hover:border-zinc-400 focus:outline-none focus:ring-4 focus:ring-accent-500/15 focus:border-accent-500 dark:border-zinc-700 dark:bg-zinc-950/70 dark:text-zinc-100 dark:hover:border-zinc-600"
                >
            </div>
        </div>

        <x-admin.submit-button busyLabel="{{ __('auth.verify_code.submit_busy') }}">{{ __('auth.verify_code.submit') }}</x-admin.submit-button>
    </form>

    <div class="mt-6 flex items-center justify-between text-sm">
        <a href="/admin/forgot-password" class="group flex items-center gap-1.5 font-medium text-zinc-500 transition-colors duration-150 hover:text-accent-600 dark:text-zinc-400 dark:hover:text-accent-400">
            <i class="ph ph-arrow-left text-base transition-transform duration-150 rtl:scale-x-[-1] group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5"></i>
            {{ __('auth.verify_code.back') }}
        </a>
        <button id="resend-code" type="button" class="link-action disabled:cursor-not-allowed disabled:text-zinc-400 disabled:after:content-none dark:disabled:text-zinc-600">
            {{ __('auth.verify_code.resend') }}
        </button>
    </div>
@endsection

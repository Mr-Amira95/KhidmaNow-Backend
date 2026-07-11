@extends('admin.layouts.auth')

@section('title', __('auth.forgot_password.title'))
@section('page', 'forgot-password')

@section('content')
    <div id="forgot-password-panel">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ __('auth.forgot_password.heading') }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                {{ __('auth.forgot_password.subheading') }}
            </p>
        </div>

        <form id="forgot-password-form" class="stagger space-y-6" novalidate>
            <x-admin.text-field
                name="login"
                label="{{ __('auth.forgot_password.login_label') }}"
                icon="ph-envelope-simple"
                autocomplete="username"
                required
            />

            <x-admin.submit-button busyLabel="{{ __('auth.forgot_password.submit_busy') }}">{{ __('auth.forgot_password.submit') }}</x-admin.submit-button>
        </form>

        <a href="/admin/login" class="group mt-6 flex items-center justify-center gap-1.5 text-sm font-medium text-zinc-500 transition-colors duration-150 hover:text-accent-600 dark:text-zinc-400 dark:hover:text-accent-400">
            <i class="ph ph-arrow-left text-base transition-transform duration-150 rtl:scale-x-[-1] group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5"></i>
            {{ __('auth.forgot_password.back_to_sign_in') }}
        </a>
    </div>

    <div id="forgot-password-success" class="hidden text-center">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-brand-green-50 dark:bg-brand-green-900/20">
            <svg viewBox="0 0 24 24" class="h-8 w-8 text-brand-green-500 dark:text-brand-green-400" fill="none" aria-hidden="true">
                <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="check-path" />
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-50">{{ __('auth.forgot_password.success_heading') }}</h2>
        <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">
            {!! __('auth.forgot_password.success_body', ['login' => '<span id="success-masked-login" class="font-medium text-zinc-700 dark:text-zinc-300"></span>']) !!}
        </p>
    </div>
@endsection

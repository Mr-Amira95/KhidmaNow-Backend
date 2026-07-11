@extends('admin.layouts.auth')

@section('title', __('auth.reset_password.title'))
@section('page', 'reset-password')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ __('auth.reset_password.heading') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">{{ __('auth.reset_password.subheading') }}</p>
    </div>

    <form id="reset-password-form" class="stagger space-y-6" novalidate>
        <x-admin.password-field
            name="password"
            label="{{ __('auth.reset_password.password_label') }}"
            autocomplete="new-password"
            required
        />

        <x-admin.password-field
            name="password_confirmation"
            label="{{ __('auth.reset_password.confirm_label') }}"
            autocomplete="new-password"
            required
        />

        <x-admin.submit-button busyLabel="{{ __('auth.reset_password.submit_busy') }}">{{ __('auth.reset_password.submit') }}</x-admin.submit-button>
    </form>

    <a href="/admin/login" class="group mt-6 flex items-center justify-center gap-1.5 text-sm font-medium text-zinc-500 transition-colors duration-150 hover:text-accent-600 dark:text-zinc-400 dark:hover:text-accent-400">
        <i class="ph ph-arrow-left text-base transition-transform duration-150 rtl:scale-x-[-1] group-hover:-translate-x-0.5 rtl:group-hover:translate-x-0.5"></i>
        {{ __('auth.reset_password.back_to_sign_in') }}
    </a>
@endsection

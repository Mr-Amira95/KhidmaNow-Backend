@extends('admin.layouts.auth')

@section('title', __('auth.login.title'))
@section('page', 'login')

@section('content')
    <div class="mb-7">
        <h1 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ __('auth.login.heading') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">{{ __('auth.login.subheading') }}</p>
    </div>

    <form id="login-form" class="stagger space-y-6" novalidate>
        <x-admin.text-field
            name="login"
            label="{{ __('auth.login.login_label') }}"
            icon="ph-envelope-simple"
            autocomplete="username"
            required
        />

        <x-admin.password-field name="password" label="{{ __('auth.login.password_label') }}" autocomplete="current-password" required>
            <a href="/admin/forgot-password" class="link-action text-sm">
                {{ __('auth.login.forgot_password') }}
            </a>
        </x-admin.password-field>

        <x-admin.checkbox-field name="remember" label="{{ __('auth.login.remember_me') }}" />

        <x-admin.submit-button busyLabel="{{ __('auth.login.submit_busy') }}">{{ __('auth.login.submit') }}</x-admin.submit-button>
    </form>
@endsection

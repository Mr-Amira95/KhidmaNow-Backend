<?php

return [
    'copyright' => 'All rights reserved.',

    'common' => [
        'generic_error' => 'Something went wrong. Please try again.',
        'network_error' => 'Could not reach the server. Check your connection and try again.',
    ],

    'login' => [
        'title' => 'Sign in',
        'heading' => 'Welcome back',
        'subheading' => 'Sign in to your KhidmaNow admin workspace.',
        'login_label' => 'Email or phone',
        'password_label' => 'Password',
        'forgot_password' => 'Forgot password?',
        'remember_me' => 'Remember me',
        'submit' => 'Sign in',
        'submit_busy' => 'Signing in',
        'reset_success' => 'Your password has been reset. Sign in with your new password.',
        'invalid_credentials' => 'That email/phone or password is incorrect.',
        'inactive_account' => 'This account is inactive. Contact an administrator.',
        'fix_fields' => 'Please fix the highlighted fields.',
    ],

    'forgot_password' => [
        'title' => 'Forgot password',
        'heading' => 'Reset your password',
        'subheading' => 'Enter the email or phone number on your admin account and we will send you a verification code.',
        'login_label' => 'Email or phone',
        'submit' => 'Send code',
        'submit_busy' => 'Sending code',
        'back_to_sign_in' => 'Back to sign in',
        'success_heading' => 'Code sent',
        'success_body' => 'We sent a verification code to :login.',
        'not_found' => 'We could not find an account with that email or phone.',
    ],

    'verify_code' => [
        'title' => 'Verify code',
        'heading' => 'Enter verification code',
        'subheading' => 'We sent a 4-digit code to :login.',
        'submit' => 'Verify code',
        'submit_busy' => 'Verifying',
        'back' => 'Back',
        'resend' => 'Resend code',
        'resend_countdown' => 'Resend code (:seconds s)',
        'resend_success' => 'A new code has been sent.',
        'resend_error' => 'Could not resend the code. Try again shortly.',
        'incomplete_code' => 'Enter the full 4-digit code.',
        'invalid_code' => 'That code is invalid or has expired.',
    ],

    'reset_password' => [
        'title' => 'Reset password',
        'heading' => 'Set a new password',
        'subheading' => 'Choose a strong password with at least 8 characters.',
        'password_label' => 'New password',
        'confirm_label' => 'Confirm new password',
        'submit' => 'Reset password',
        'submit_busy' => 'Resetting',
        'back_to_sign_in' => 'Back to sign in',
        'password_too_short' => 'Password must be at least 8 characters.',
        'password_mismatch' => 'Passwords do not match.',
        'expired' => 'That code has expired. Please request a new one.',
    ],
];

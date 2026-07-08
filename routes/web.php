<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'admin.auth.login')->name('login');
    Route::view('/forgot-password', 'admin.auth.forgot-password')->name('forgot-password');
    Route::view('/verify-code', 'admin.auth.verify-code')->name('verify-code');
    Route::view('/reset-password', 'admin.auth.reset-password')->name('reset-password');
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
});

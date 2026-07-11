<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    if (in_array($locale, ['en', 'ar'], true)) {
        $request->session()->put('locale', $locale);
    }

    $redirect = $request->query('redirect');
    $allowed = [
        '/admin/login',
        '/admin/forgot-password',
        '/admin/verify-code',
        '/admin/reset-password',
    ];

    return redirect(in_array($redirect, $allowed, true) ? $redirect : '/admin/login');
})->name('locale.switch');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/login', 'admin.auth.login')->name('login');
    Route::view('/forgot-password', 'admin.auth.forgot-password')->name('forgot-password');
    Route::view('/verify-code', 'admin.auth.verify-code')->name('verify-code');
    Route::view('/reset-password', 'admin.auth.reset-password')->name('reset-password');
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    Route::view('/users/clients', 'admin.users.clients')->name('users.clients');
    Route::view('/users/providers', 'admin.users.providers')->name('users.providers');

    Route::view('/categories', 'admin.categories.index')->name('categories');

    Route::view('/chats', 'admin.chats.index')->name('chats');
    Route::view('/support-tickets', 'admin.support-tickets.index')->name('support-tickets');
    Route::view('/notifications/send', 'admin.notifications.send')->name('notifications.send');
    Route::view('/notifications', 'admin.notifications.index')->name('notifications.index');

    Route::view('/locations/countries', 'admin.locations.countries')->name('locations.countries');
    Route::view('/locations/cities', 'admin.locations.cities')->name('locations.cities');
    Route::view('/locations/areas', 'admin.locations.areas')->name('locations.areas');

    Route::view('/cms/intro-screens', 'admin.cms.intro-screens')->name('cms.intro-screens');
    Route::view('/cms/terms', 'admin.cms.terms')->name('cms.terms');
    Route::view('/cms/privacy-policy', 'admin.cms.privacy-policy')->name('cms.privacy-policy');
    Route::view('/cms/faqs', 'admin.cms.faqs')->name('cms.faqs');
});

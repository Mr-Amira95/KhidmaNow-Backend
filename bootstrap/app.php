<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'provider'    => \App\Http\Middleware\ProviderMiddleware::class,
            'customer'    => \App\Http\Middleware\CustomerMiddleware::class,
            'permission'  => \App\Http\Middleware\PermissionMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

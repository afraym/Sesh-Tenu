<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'super.admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
            'manage.all' => \App\Http\Middleware\EnsureUserCanManageAll::class,
            'company.owner' => \App\Http\Middleware\EnsureUserIsCompanyOwner::class,
            'company.subscription' => \App\Http\Middleware\EnsureUserHasActiveSubscription::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocale::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

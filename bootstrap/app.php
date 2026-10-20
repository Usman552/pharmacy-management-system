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
    ->withCommands([__DIR__.'/../app/Console/Commands'])
    ->withMiddleware(function (Middleware $middleware) {
        // Register admin middleware alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'role'  => \App\Http\Middleware\EnsureRole::class,
        ]);

        // Behind a proxy / tunnel / load balancer the original https scheme and host come in forwarded headers.
        $middleware->trustProxies(at: '*');

        // Check the role before route-model binding so outsiders get 403, not a 404 that reveals what exists.
        $middleware->prependToPriorityList(
            before: \Illuminate\Routing\Middleware\SubstituteBindings::class,
            prepend: \App\Http\Middleware\EnsureRole::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

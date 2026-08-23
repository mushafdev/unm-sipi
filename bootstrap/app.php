<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AuthenticationCheck;
use App\Http\Middleware\PermissionOrDosen;
use App\Http\Middleware\DenyDosen;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth-check'=>AuthenticationCheck::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'permission-or-dosen' => PermissionOrDosen::class,
            'deny-dosen' => DenyDosen::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

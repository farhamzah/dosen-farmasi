<?php

use App\Console\Commands\DosenStorageCheckCommand;
use App\Console\Commands\IntegrationClientTokenCommand;
use App\Console\Commands\IntegrationHealthCommand;
use App\Console\Commands\SeedUiDemoCommand;
use App\Console\Commands\SyncIntegrationsCommand;
use App\Http\Middleware\AuthenticateIntegrationClient;
use App\Http\Middleware\EnsureDosenRole;
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
    ->withCommands([
        DosenStorageCheckCommand::class,
        IntegrationClientTokenCommand::class,
        IntegrationHealthCommand::class,
        SeedUiDemoCommand::class,
        SyncIntegrationsCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'dosen.role' => EnsureDosenRole::class,
            'integration.client' => AuthenticateIntegrationClient::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

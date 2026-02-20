<?php

use App\Exceptions\MovieDatabaseException;
use App\Exceptions\MovieNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (MovieNotFoundException $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 404);
        });

        $exceptions->renderable(function (MovieDatabaseException $e, Request $request) {
            return response()->json(['error' => $e->getMessage()], 500);
        });
    })->create();

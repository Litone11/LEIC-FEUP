<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
// use Throwable;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthorizationException $exception, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return response()->view('errors.403', [], 403);
        });

        $exceptions->render(function (NotFoundHttpException $exception, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return response()->view('errors.404', [], 404);
        });

        $exceptions->render(function (Throwable $exception, $request) {
            if ($request->expectsJson()) {
                return null;
            }

            if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() >= 500) {
                return response()->view('errors.500', [], 500);
            }

            return null;
        });
    })->create();



    


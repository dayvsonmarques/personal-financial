<?php

use App\Http\Middleware\ResolveCurrentOrganization;
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
        // Sem views no Laravel (D21): convidados vão para a tela de login da SPA.
        $middleware->redirectGuestsTo('/entrar');
        $middleware->alias([
            'org' => ResolveCurrentOrganization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // O link de verificação de e-mail é a única URL da API aberta direto no navegador.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson()
                || ($request->is('api/*') && ! $request->is('api/v1/auth/email/verify/*')),
        );
    })->create();

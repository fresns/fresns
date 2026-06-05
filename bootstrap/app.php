<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: [
            'fresns_panel_locale',
            'fresns_post_message_key',
            'fresns_redirect_url',
            'fresns_timezone',
            'fresns_lang_tag',
            'fresns_aid',
            'fresns_aid_token',
            'fresns_uid',
            'fresns_uid_token',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })
    ->withSingletons([
        Illuminate\Contracts\Console\Kernel::class => App\Console\Kernel::class
    ])->create();

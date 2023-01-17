<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Exceptions;

use App\Helpers\ConfigHelper;
use App\Models\Plugin;
use Browser;
use Fresns\DTO\Exceptions\DTOException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        ApiException::class,
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof DTOException) {
            throw new DTOException($e->getMessage());
        }

        if ($e instanceof ValidationException) {
            if (! $request->wantsJson()) {
                return back()->with('failure', $e->validator->errors()->first());
            }

            throw new RuntimeException($e->validator->errors()->first());
        }

        if ($e instanceof NotFoundHttpException) {
            $engine = Plugin::type(Plugin::TYPE_ENGINE)->isEnable()->first();

            if (! $engine) {
                return parent::render($request, $e);
            }

            $mobileTheme = ConfigHelper::fresnsConfigByItemKey("{$engine->unikey}_Mobile");
            $desktopTheme = ConfigHelper::fresnsConfigByItemKey("{$engine->unikey}_Desktop");

            $theme = Browser::isMobile() ? $mobileTheme : $desktopTheme;

            if (! $theme) {
                return parent::render($request, $e);
            }

            $finder = app('view')->getFinder();
            $finder->prependLocation(base_path("extensions/themes/{$theme}"));

            return Response::view(404, [
                'engineUnikey' => $engine->unikey,
                'themeUnikey' => $theme,
            ], 404);
        }

        return parent::render($request, $e);
    }
}

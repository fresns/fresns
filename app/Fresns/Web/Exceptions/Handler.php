<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Exceptions;

use App\Fresns\Api\Traits\ApiResponseTrait;
use Illuminate\Http\Exceptions\HttpResponseException;

class Handler
{
    use ApiResponseTrait;

    public function handle(\Throwable $e)
    {
        if ($e instanceof HttpResponseException) {
            throw $e;
        }

        $this->report($e);

        return $this->render($e);
    }

    public function report(\Throwable $exception)
    {
        report($exception);
    }

    public function render(\Throwable $exception)
    {
        if (\request()->wantsJson()) {
            return $this->failure(3e4, $exception->getMessage());
        }

        return back()
            ->with([
                'code' => $exception->getCode(),
                'failure' => preg_replace('/ApiException: /', '', $exception->getMessage()),
            ]);
    }
}

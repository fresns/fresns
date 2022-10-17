<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Exceptions;

use Illuminate\Support\Facades\Response;

class ErrorException extends \Exception
{
    public function render()
    {
        if (\request()->wantsJson()) {
            return \response()->json([
                'code' => $this->getCode(),
                'message' => $this->getMessage(),
            ]);
        }

        // 404 Not Found
        if (in_array($this->getCode(), [
            37100, 37200, 37300, 37302, 37400, 37402, 38100,
        ])) {
            return Response::view('error', [
                'code' => $this->getCode(),
                'message' => $this->getMessage(),
            ], 404);
        }

        // 403 Forbidden
        if (in_array($this->getCode(), [
            36201, 37101, 37201, 37301, 37401,
        ])) {
            return Response::view('error', [
                'code' => $this->getCode(),
                'message' => $this->getMessage(),
            ], 403);
        }

        // 500 Internal Server Error
        if (in_array($this->getCode(), [
            31000,
            31101, 31102, 31103,
            31201, 31202,
            31301, 31302, 31303, 31304,
            31401, 31402,
            31501, 31502, 31503, 31504, 31505,
            31601, 31602, 31603,
            31701, 31702, 31703,
            34201, 36300,
        ])) {
            return Response::view('error', [
                'code' => $this->getCode(),
                'message' => $this->getMessage(),
            ], 500);
        }

        // Other
        return back()->with([
            'code' => $this->getCode(),
            'failure' => $this->getMessage(),
        ]);
    }
}

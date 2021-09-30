<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Traits;

use App\Http\Center\Common\ErrorCodeService;

trait CodeTrait
{
    public function getCodeMap()
    {
        return $this->codeMap;
    }

    public static function checkInfo($code)
    {
        $message = ErrorCodeService::getMsg($code);
        $data = [
            'code' => $code,
            'msg' => $message ?? 'Function Check Anomalies',
        ];

        return $data;
    }
}

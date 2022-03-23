<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Common;

use Illuminate\Support\Facades\Log;

class LogService
{
    public static function formatInfo($info)
    {
        if (is_object($info)) {
            $object = json_decode(json_encode($info), true);

            return  $object;
        }

        return $info;
    }

    public static function info($msg, $info = [])
    {
        $traceInfo = self::getTraceInfo(debug_backtrace());
        $info = self::formatInfo($info);
        $arr = ['trace' => $traceInfo, 'msg' => $msg, 'info' => $info];
        Log::info($arr);
    }

    public static function warning($msg, $info = [])
    {
        $traceInfo = self::getTraceInfo(debug_backtrace());
        $info = self::formatInfo($info);
        $arr = ['trace' => $traceInfo, 'msg' => $msg, 'info' => $info];
        Log::warning($arr);
    }

    public static function error($msg, $info = [])
    {
        $traceInfo = self::getTraceInfo(debug_backtrace());
        $info = self::formatInfo($info);
        $arr = ['trace' => $traceInfo, 'msg' => $msg, 'info' => $info];
        Log::error($arr);
    }

    public static function getTraceInfo($bt)
    {
        $info = $bt[0];
        if (isset($info['file'])) {
            $info['file'] = str_replace(base_path().'/', '', $info['file']);
        }
        $traceInfo = $info['file'].':'.$info['line'];

        return $traceInfo;
    }
}

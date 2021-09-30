<?php
/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Common;

use App\Helpers\DBHelper;
use App\Traits\ApiTrait;
use App\Traits\ServerTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ValidateService
{
    use ApiTrait;

    public static $validator = null;

    public static function validateRule(Request $request, $rule, $message = [])
    {
        self::$validator = \Validator::make($request->all(), $rule, $message);
        if (self::$validator->fails()) {
            self::showError();
        }
    }

    public static function validateRuleDirect($inputArr, $rule, $message = [])
    {
        self::$validator = \Validator::make($inputArr, $rule, $message);
        if (self::$validator->fails()) {
            self::showError();
        }
    }

    public static function showError()
    {
        $data = self::$validator->errors();
        (new self)->error(ErrorCodeService::CODE_PARAM_ERROR, $data);
    }

    // Rules for calibration services
    public static function validateServerRule($params, $rule)
    {
        self::$validator = \Validator::make($params, $rule);
        if (self::$validator->fails()) {
            $info = self::$validator->errors();

            return $info;
        }

        return true;
    }

    protected static $validMap = [
        'default.enable' => [
            'id' => 'required',
        ],
    ];

    // Check if the id exists in the table
    public static function existInTable($idArr, $table)
    {
        if (! is_array($idArr)) {
            return false;
        }

        if (count($idArr) == 0) {
            return false;
        }
        $conn = DBHelper::getConnectionName($table);

        $queryCount = DB::connection($conn)->table($table)->whereIn('id', $idArr)->count();

        return count($idArr) === $queryCount;
    }

    // Check if the ids exists in the table
    public static function idsStrExistInTable($idStr, $table)
    {
        $idArr = explode(',', $idStr);

        if (count($idArr) == 0) {
            return false;
        }

        $queryCount = DB::table($table)->whereIn('id', $idArr)->count();

        return count($idArr) === $queryCount;
    }

    //  Validate array fields
    public static function validParamExist($params, $checkParamsArr)
    {
        foreach ($checkParamsArr as $v) {
            if (! isset($params[$v]) || $params[$v] == '') {
                LogService::error("Parameter checksum failure [$v] ", $params);
                LogService::error('The calibration field is: ', $checkParamsArr);

                return false;
                // (new self)->error(ErrorCodeService::DATA_EXCEPTION_ERROR, $data);
            }
        }

        return true;
    }
}

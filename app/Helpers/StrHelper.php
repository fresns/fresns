<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Illuminate\Support\Str;

class StrHelper
{
    // Random String
    public static function randString($length = 10)
    {
        return Str::random($length);
    }

    // Random String
    public static function randOrderNo($prefix = 'BD')
    {
        $t = date('YmdHis', time());

        return $prefix.$t.rand(100, 999).rand(10000, 99999);
    }

    //Remove all Chinese characters from a string
    public static function replaceZh($str)
    {
        $str = preg_replace('/([\x80-\xff]*)/i', '', $str);

        return $str;
    }

    // Determine if it is true
    public static function isTrue($val, $return_null = false)
    {
        $boolVal = (is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val);

        return  $boolVal === null && ! $return_null ? false : $boolVal;
    }

    // Random SMS Code
    public static function randSmsCode($length = 6)
    {
        $smsCode = rand(100000, 999999);

        return $smsCode;
    }

    // Create Token
    public static function createToken($length = 30)
    {
        return Str::random($length);
    }

    // Create Password
    public static function createPassword($str)
    {
        return password_hash($str, PASSWORD_BCRYPT);
    }

    // Create Phone
    public static function createPhone($phone)
    {
        return substr_replace($phone, '****', 3, 4);
    }

    // Encrypt Phone
    public static function encryptPhone($phone, $start = 3, $end = 6)
    {
        if (empty($phone)) {
            return '';
        }

        return substr_replace($phone, '****', $start, $end);
    }

    // Email Encryption
    public static function encryptEmail($email)
    {
        if (empty($email)) {
            return '';
        }
        $emailArr = explode('@', $email);

        $email = null;
        if ($emailArr) {
            $email1 = substr_replace($emailArr[0], '***', 3);
            if (empty($email1)) {
                return '';
            }
            $email = $email1.'@'.$emailArr[1];
        }

        return $email;
    }

    // Real Name Encryption
    public static function encryptName($name)
    {
        $name = mb_substr($name, -1, 1);

        $name = '*'.$name;

        return $name;
    }

    // Real ID Number Encryption
    public static function encryptIdNumber($number, $startNum = 1, $endNum = 1)
    {
        $num = strlen($number);
        $count = $startNum + $endNum;
        $num = $num - $count;
        $str = '';
        $str = sprintf("%'*".$num.'s', $str);
        $start = mb_substr($number, 0, $startNum);
        $end = mb_substr($number, $endNum);

        return $start.$str.$end;
    }

    // Generate Unique Number
    public static function createUuid($length = 8)
    {
        $str = Str::random($length);
        $str = strtolower($str);

        return $str;
    }

    /**
     * Check digit
     * The check digit is calculated as follows:
     *   Take the sum of the odd bits of the number
     *   Take the sum of the even digits of the number
     *   Add the sum of the odd digits to the "triple of the sum of the even digits"
     *   Take the number of digits of the result
     *   Subtract this digit from 10
     *   Take the resulting number to the single digit again.
     */
    public static function createNumber($rand)
    {
        $randArr = str_split($rand);
        $oddNumberArr = [];
        $evenNumber = [];
        foreach ($randArr as $k => $v) {
            $num = $k + 1;
            if ($num % 2 == 0) {
                $evenNumber[] = $v;
            } else {
                $oddNumberArr[] = $v;
            }
        }

        $number = array_sum($oddNumberArr) + array_sum($evenNumber) * 3;

        $number = substr($number, '-1');

        $number = 10 - intval($number);

        $number = substr($number, '-1');

        return $number;
    }

    // Query condition de-duplication
    // Filter Data
    public static function SearchIntersect($intersectArr)
    {
        $arr = [];
        foreach ($intersectArr as $v) {
            $arr = array_merge($arr, $v);
        }
        if (empty($arr)) {
            return 0;
        }

        $idArr = implode(',', array_unique($arr));

        return $idArr;
    }

    // Determine if it is json
    public static function isJson($json_str)
    {
        try {
            if (is_array(json_decode($json_str, true))) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    // String Trimming
    public static function cropContent($content, $cropLength)
    {
        $len = $cropLength * 2;

        $str = mb_strimwidth($content, 0, $len, '...', 'utf8');

        return  $str;
    }
}

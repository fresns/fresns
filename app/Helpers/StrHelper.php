<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

class StrHelper
{
    /**
     * @param  string  $email
     * @return string
     */
    public static function encryptEmail(string $email)
    {
        $emailArr = explode('@', $email);
        if (empty($emailArr[0])) {
            return '';
        }
        $mid = str_repeat('*', strlen($emailArr[0]) - 3);
        $emailStr = substr_replace($emailArr[0], $mid, 3);
        $email = $emailStr.'@'.$emailArr[1];

        return $email;
    }

    /**
     * @param  int  $number
     * @return mixed
     */
    public static function encryptNumber(int $number)
    {
        $head = substr($number, 0, 2);
        $tail = substr($number, -2);
        $starCount = strlen($number) - 4;
        $star = str_repeat('*', $starCount);

        return $head.$star.$tail;
    }

    /**
     * @param  string  $name
     * @return string
     */
    public static function encryptName(string $name)
    {
        $len = mb_strlen($name);
        if ($len < 1) {
            return $name;
        }
        $last = mb_substr($name, -1, 1);
        $lastName = str_repeat('*', $len - 1);

        return $lastName.$last;
    }

    /**
     * @param  int  $length
     * @return int
     */
    public static function generateDigital(int $length = 6)
    {
        return rand(pow(10, ($length - 1)), pow(10, $length) - 1);
    }

    /**
     * @param string $uri
     * @param string $domain
     */
    public static function qualifyUrl(?string $uri = null, ?string $domain = null)
    {
        if (!$uri) {
            return '';
        }

        if (!$domain) {
            $defaultDomain = config('app.url');

            return sprintf('%s/%s', $defaultDomain, ltrim($uri, '/'));
        }

        return sprintf('%s/%s', rtrim($domain, '/'), ltrim($uri, '/'));
    }

    /**
     * @param  string  $commaString
     * @return array
     */
    public static function commaStringToArray(string $commaString = '')
    {
        $toArray = explode(',', $commaString);

        return $toArray;
    }
}

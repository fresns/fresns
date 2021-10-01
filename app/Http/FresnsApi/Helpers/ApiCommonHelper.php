<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Helpers;

use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsStopWords\FresnsStopWords;
use Illuminate\Support\Str;

class ApiCommonHelper
{
    // Phone Encryption
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

    // Generate UUID
    public static function createUuid($length = 8)
    {
        $str = Str::random($length);
        $str = strtolower($str);

        return $str;
    }

    // Generate mid (Pure Digital)
    public static function createMemberUuid()
    {
        $uuid = rand(10000000, 99999999);

        // Check if there are duplicates of
        $count = FresnsMembers::where('uuid', $uuid)->count();
        if ($count > 0) {
            $uuid = rand(10000000, 99999999);
        }

        return $uuid;
    }

    /**
     * The full URL address of the plugin, stitched together from the domain name field plugin_domain plus the path field access_path.
     * When plugin_domain is empty, it is spliced with the backend address (configuration table key name backend_domain) to form the full URL address.
     * If the address has a {parameter} variable name, use the record plugin_usages > parameter to replace the field value.
     */
    public static function getPluginUsagesUrl($pluginUnikey, $pluginUsagesid)
    {
        $bucketDomain = ApiConfigHelper::getConfigByItemKey(FsConfig::BACKEND_DOMAIN);
        $pluginUsages = FresnsPluginUsages::find($pluginUsagesid);
        $plugin = FresnsPlugins::where('unikey', $pluginUnikey)->first();
        $url = '';
        if (! $plugin || ! $pluginUsages) {
            return $url;
        }
        $access_path = $plugin['access_path'];
        $str = strstr($access_path, '{parameter}');
        if ($str) {
            $uri = str_replace('{parameter}', $pluginUsages['parameter'], $access_path);
        } else {
            $uri = $access_path;
        }
        if (empty($plugin['plugin_url'])) {
            $url = $bucketDomain.$uri;
        } else {
            $url = $plugin['plugin_domain'].$uri;
        }
        $url = self::getImageSignUrl($url);

        return $url;
    }

    // Stop Word Rules
    public static function messageStopWords($text)
    {
        $stopWordsArr = FresnsStopWords::get()->toArray();

        foreach ($stopWordsArr as $v) {
            $str = strstr($text, $v['word']);
            if ($str != false) {
                if ($v['dialog_mode'] == 2) {
                    $text = str_replace($v['word'], $v['replace_word'], $text);
                    return $text;
                }
                if ($v['dialog_mode'] == 3) {
                    return false;
                }
            }
        }

        return $text;
    }
}

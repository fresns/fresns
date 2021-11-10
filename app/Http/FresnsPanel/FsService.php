<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Services\BaseAdminService;
use App\Http\UpgradeController;
use Illuminate\Support\Facades\Log;

class FsService extends BaseAdminService
{
    // Get the current setting language
    public static function getLanguage($lang)
    {
        $map = FsConfig::LANGUAGE_MAP;

        return $map[$lang] ?? 'English - English';
    }

    /**
     * version check
     */
    public static function getVersionInfo(){
        $url = FsConfig::VERSION_URL;
        $rs = self::httpGet($url);
        $api_version =  !empty($rs) ? json_decode($rs,true) : [];
        $current_version = UpgradeController::$versionInt;
        if(isset($api_version['versionInt']) && $api_version['versionInt'] > $current_version){
            return ['currentVersion'=>UpgradeController::$version,'canUpgrade'=>true,'upgradeVersion'=>$api_version['version'],'upgradePackage'=>$api_version['upgradePackage']];
        }else{
            return ['currentVersion'=>UpgradeController::$version,'canUpgrade'=>false,'upgradeVersion'=>UpgradeController::$version,'upgradePackage'=>''];
        }
    }

    public static function httpGet($url, $timeout = 60, $headers = [])
    {
        $starttime = microtime(true);
        $log = [];
        $log['api'] = $url;
        $log['method'] = 'GET';
        $log['param'] = [];
        $log['response'] = '';
        $log['speed'] = 0;
        $log['request_url'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $log['request_ip'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (defined('SWOOLE_IP') ? SWOOLE_IP : '');
        $log['datetime'] = 0;
        $log['status'] = 0;
        $httpheader = [];
        foreach ($headers as $k => $v) {
            array_push($httpheader, $k . ': ' . $v);
        }
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        if ($httpheader) {
            curl_setopt($oCurl, CURLOPT_HEADER, 0);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $httpheader);
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        $sContent = curl_exec($oCurl);
        if ($sContent === false) {
            if (curl_errno($oCurl) == CURLE_OPERATION_TIMEOUTED) {
                $sContent = 'CURLE_OPERATION_TIMEOUTED';
            }
        }
        curl_close($oCurl);
        $log['response'] = $sContent;
        $log['speed'] = number_format(microtime(true) - $starttime, 5);
        $log['datetime'] = $starttime;
        $log['status'] = 1;
        Log::info('upgrate-http',$log);
        return $sContent;
    }

    public static function downFile($url,$filePath,$timeout=600)
    {
        $ch = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);

        file_put_contents($filePath, $content);
        unset($content);
        return true;
    }


}

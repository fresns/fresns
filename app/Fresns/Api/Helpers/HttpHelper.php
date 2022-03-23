<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class HttpHelper
{
    /**
     * Initiate a request.
     *
     * @param $url
     * @param  array  $postData
     * @param  string  $method
     * @param  bool  $useJson
     * @return mixed|array
     */
    public static function postFetch($url, $postFields = [], $header = [])
    {
        $postFields = json_encode($postFields);

        $ch = curl_init();
        $content = ['Content-Type: application/json; charset=utf-8'];
        if ($header) {
            $content = array_merge($content, $header);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $content);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // If the error "name lookup timed out" is reported, add this line of code
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);

        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = 'Request Status: '.$rsp.' '.curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);

        return $result;
    }

    public static function getFetch($url, $postData = [], $method = 'GET', $useJson = true)
    {
        $client = new \GuzzleHttp\Client();

        try {
            return json_decode($client->request($method, $url, [$useJson ? 'json' : 'form_params' => $postData])->getBody()->getContents(), true);
        } catch (RequestException $e) {
            Log::error('Request Exception: '.$e->getCode().','.$e->getMessage());
        }
    }

    /**
     * Initiate a request.
     *
     * @param $url
     * @param  array  $postData
     * @param  string  $method
     * @param  bool  $useJson
     * @return mixed|array
     */
    public static function post($url, $dataArr = [], $header = [])
    {
        $postFields = json_encode($dataArr);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // If the error "name lookup timed out" is reported, add this line of code
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = 'Request Status: '.$rsp.' '.curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);

        return $result;
    }

    // Get Path
    public static function getParseUrl()
    {
        $menu_path_arr = parse_url(url()->previous());
        $menu_path = isset($menu_path_arr['path']) ? $menu_path_arr['path'] : null;

        return $menu_path;
    }

    // Post Request
    public static function guzzleHttpPost($url, $params, $headers = [])
    {

        // Send Request
        $client = new \GuzzleHttp\Client();
        $respData = $client->request('post',
            $url, [
                'json'  => $params,
                'headers' => $headers,
            ])
            ->getBody()->getContents();

        $resArr = json_decode($respData, true);

        return $resArr;
    }

    public static function curl($url, $postData = [], $file = '')
    {
        // 1. Initializing curl connection
        $ch = curl_init();

        // 2. Set each parameter
        // Start curl
        $ch = curl_init();
        // Set the request URL
        curl_setopt($ch, CURLOPT_URL, $url);
        // Do not get header information
        curl_setopt($ch, CURLOPT_HEADER, 0);
        // The results are not returned directly to the terminal
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // Set curl to not perform certificate detection
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // Timeout time (second)
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        // Set the requested browser
        curl_setopt($ch, CURLOPT_ACCOUNTAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36');
        // Initiate a POST request
        curl_setopt($ch, CURLOPT_POST, 1);
        // The data sent by post, note that http_build_query can format the $postData array data into the format of http transfer data
        // http_build_query This function is recommended to be added when simply passing post data, note that it does not contain file data, otherwise there may be instability in the transfer of data
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        // 3. Execute curl connection
        $data = curl_exec($ch);
        // Get information about the execution of curl connection
        $info = curl_getinfo($ch);

        // 4. Close curl connection
        curl_close($ch);

        if ($info['http_code'] == '200') {
            // Only when the response status code is 200 is it necessary to return the result of the execution
            return $data;
        }

        // If the value of the response status code is not 200, false is returned
        return false;
    }

    // Request External URL
    public static function curlRequest($url, $mothed = 'GET', $data = [])
    {
        $ch = curl_init();
        $header = 'Accept-Charset: utf-8';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mothed);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ACCOUNTAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $temp = curl_exec($ch);
        curl_close($ch);

        return $temp;
    }
}

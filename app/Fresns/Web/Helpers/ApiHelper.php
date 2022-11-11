<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Helpers;

use App\Fresns\Client\Clientable;
use App\Fresns\Web\Exceptions\ErrorException;
use App\Helpers\ConfigHelper;
use App\Helpers\SignHelper;
use App\Models\SessionKey;
use App\Utilities\AppUtility;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class ApiHelper
{
    use Clientable;

    protected array $result = [];

    public function caseForwardCallResult($result)
    {
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            throw new ErrorException(session('failure'), session('code'));
        }

        return $result;
    }

    public function caseUnwrapRequests(array $results)
    {
        if ($results instanceof \Illuminate\Http\RedirectResponse) {
            throw new ErrorException(session('failure'), (int) session('code'));
        }

        return $results;
    }

    public function paginate()
    {
        if (! data_get($this->result, 'data.paginate', false)) {
            return null;
        }

        $paginate = new LengthAwarePaginator(
            items: data_get($this->result, 'data.list'),
            total: data_get($this->result, 'data.paginate.total'),
            perPage: data_get($this->result, 'data.paginate.pageSize'),
            currentPage: data_get($this->result, 'data.paginate.currentPage'),
        );

        $paginate
            ->withPath('/'.\request()->path())
            ->withQueryString();

        return $paginate;
    }

    public function getBaseUri(): ?string
    {
        $engineApiType = ConfigHelper::fresnsConfigByItemKey('engine_api_type');

        $isLocal = true;
        if ($engineApiType == 'remote') {
            $isLocal = false;
        }

        $localApiHost = config('app.url');
        $remoteApiHost = ConfigHelper::fresnsConfigByItemKey('engine_api_host');

        $apiHost = $isLocal ? $localApiHost : $remoteApiHost;

        return $apiHost;
    }

    public function getOptions()
    {
        $apiHost = Cache::rememberForever('fresns_web_api_host', function () {
            return $this->getBaseUri();
        });

        return [
            'base_uri' => $apiHost,
            'timeout' => 30000, // Request 5s timeout
            'http_errors' => false,
            'headers' => ApiHelper::getHeaders(),
        ];
    }

    public function castResponse($response)
    {
        $content = $response->getBody()->getContents();

        $data = json_decode($content, true) ?? [];

        if (empty($data)) {
            info('empty response, ApiException: '.var_export($content, true));
            throw new ErrorException($response?->getReasonPhrase(), $response?->getStatusCode());
        }

        if (! array_key_exists('code', $data)) {
            $code = 500;

            $message = $data['message'] ?? $data['exception'] ?? '';

            if (!empty($data['trace'])) {
                $message = json_encode([
                    'file' => $data['file'] ?? null,
                    'line' => $data['line'] ?? null,
                    'message' => $message
                ], \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);

                $message = str_replace([base_path().'/', '\"'], '', $message);
                $message = str_replace([base_path().'/', '\\\\'], '\\', $message);
            }

            throw new ErrorException($message, $code);
        }

        if (array_key_exists('code', $data) && $data['code'] != 0) {
            info('error response, ApiException: '.var_export($content, true));

            $message = $data['message'] ?? $data['exception'] ?? '';
            if (empty($message)) {
                $message = 'Unknown api error';
            } elseif ($data['data'] ?? null) {
                $message = "{$message} ".head($data['data']) ?? '';
            }

            throw new ErrorException($message, $data['code']);
        }

        return $data;
    }

    public static function getHeaders()
    {
        $keyConfig = Cache::rememberForever('fresns_web_api_key', function () {
            $engineApiType = ConfigHelper::fresnsConfigByItemKey('engine_api_type');

            if ($engineApiType == 'local') {
                $keyId = ConfigHelper::fresnsConfigByItemKey('engine_key_id');
                $keyInfo = SessionKey::find($keyId);

                $platformId = $keyInfo?->platform_id;
                $appId = $keyInfo?->app_id;
                $appSecret = $keyInfo?->app_secret;
            } else {
                $platformId = 4;
                $appId = ConfigHelper::fresnsConfigByItemKey('engine_api_app_id');
                $appSecret = ConfigHelper::fresnsConfigByItemKey('engine_api_app_secret');
            }

            return [
                'platformId' => $platformId,
                'appId' => $appId,
                'appSecret' => $appSecret,
            ];
        });

        $headers = [
            'Accept' => 'application/json',
            'platformId' => $keyConfig['platformId'],
            'version' => '2.0.0',
            'appId' => $keyConfig['appId'],
            'timestamp' => now()->unix(),
            'sign' => null,
            'langTag' => current_lang_tag(),
            'timezone' => Cookie::get('timezone') ?: ConfigHelper::fresnsConfigByItemKey('default_timezone'),
            'aid' => Cookie::get('fs_aid', \request('fs_aid')),
            'uid' => Cookie::get('fs_uid', \request('fs_uid')),
            'token' => Cookie::get('fs_uid_token', \request('fs_uid_token')) ?? Cookie::get('fs_aid_token', \request('fs_aid_token')),
            'deviceInfo' => json_encode(AppUtility::getDeviceInfo()),
        ];
        $headers['sign'] = SignHelper::makeSign($headers, $keyConfig['appSecret']);

        return $headers;
    }

    public static function getUploadInfo(?int $usageType = null, ?string $tableName = null, ?string $tableColumn = null, ?int $tableId = null, ?string $tableKey = null)
    {
        $uploadInfo = [
            'image' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'image',
            ],
            'video' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'video',
            ],
            'audio' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'audio',
            ],
            'document' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'document',
            ],
        ];

        return $uploadInfo;
    }
}

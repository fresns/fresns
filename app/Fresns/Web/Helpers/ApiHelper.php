<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Helpers;

use App\Fresns\Client\Clientable;
use App\Helpers\ConfigHelper;
use App\Helpers\SignHelper;
use App\Models\SessionKey;
use App\Utilities\AppUtility;
use Illuminate\Support\Facades\Cookie;
use Psr\Http\Message\ResponseInterface;

class ApiHelper implements \ArrayAccess
{
    use Clientable;

    protected array $result = [];

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
        return [
            'base_uri' => $this->getBaseUri(),
            'timeout' => 5, // Request 5s timeout
            'http_errors' => false,
            'headers' => ApiHelper::getHeaders(),
        ];
    }

    public function handleEmptyResponse(?string $content = null, ?ResponseInterface $response = null)
    {
        info('empty response, ApiException: '.var_export($content, true));
        throw new \Exception(sprintf('ApiException: %s', $response?->getReasonPhrase()), $response?->getStatusCode());
    }

    public function isErrorResponse(array $data): bool
    {
        if (! isset($data['code'])) {
            return true;
        }

        return $data['code'] !== 0;
    }

    public function handleErrorResponse(?string $content = null, array $data = [])
    {
        info('error response, ApiException: '.var_export($content, true));
        throw new \Exception(sprintf('ApiException: %s', $data['message'] ?? $data['exception'] ?? 'Unknown api error'), $data['code'] ?? 0);
    }

    public function hasPaginate(): bool
    {
        return (bool) $this['data.paginate'];
    }

    public function getTotal(): ?int
    {
        return $this['data.paginate.total'];
    }

    public function getPageSize(): ?int
    {
        return $this['data.paginate.pageSize'];
    }

    public function getCurrentPage(): ?int
    {
        return $this['data.paginate.currentPage'];
    }

    public function getLastPage(): ?int
    {
        return $this['data.paginate.lastPage'];
    }

    public function getDataList(): static|array|null
    {
        return $this['data.list']->toArray();
    }

    public static function getHeaders()
    {
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

        $headers = [
            'Accept' => 'application/json',
            'platformId' => $platformId,
            'version' => '2.0.0',
            'appId' => $appId,
            'timestamp' => now()->unix(),
            'sign' => null,
            'langTag' => \App::getLocale(),
            'timezone' => urldecode(Cookie::get('timezone')) ?? ConfigHelper::fresnsConfigByItemKey('default_timezone'),
            // 'aid' => 'fresns',
            // 'uid' => 123456,
            // 'token' => '2rPWjgayYqR5WHkrmaq2M78Q50D4WosX',
            'aid' => Cookie::get('aid') ?? null,
            'uid' => Cookie::get('uid') ?? null,
            'token' => Cookie::get('token') ?? null,
            'deviceInfo' => AppUtility::getDeviceInfo(),
        ];
        $headers['sign'] = SignHelper::makeSign($headers, $appSecret);

        return $headers;
    }
}

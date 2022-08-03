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
use Illuminate\Support\Facades\Cookie;
use Psr\Http\Message\ResponseInterface;

class ApiHelper implements \ArrayAccess, \IteratorAggregate, \Countable
{
    use Clientable {
        __call as forwardCall;
    }

    protected array $result = [];

    public function __call(string $method, array $args)
    {
        $response = $this->forwardCall($method, $args);

        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            throw new ErrorException(session('failure'), session('code'));
        }

        return $response;
    }

    public function handleUnwrap(array $requests)
    {
        $results = $this->unwrap($requests);

        if ($results instanceof \Illuminate\Http\RedirectResponse) {
            throw new ErrorException(session('failure'), (int) session('code'));
        }

        return $results;
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
        return [
            'base_uri' => $this->getBaseUri(),
            'timeout' => 30, // Request 5s timeout
            'http_errors' => false,
            'headers' => ApiHelper::getHeaders(),
        ];
    }

    public function handleEmptyResponse(?string $content = null, ?ResponseInterface $response = null)
    {
        info('empty response, ApiException: '.var_export($content, true));
        throw new ErrorException($response?->getReasonPhrase(), $response?->getStatusCode());
    }

    public function isErrorResponse(array $data): bool
    {
        if (! isset($data['code'])) {
            return true;
        }

        return false;
    }

    public function handleErrorResponse(?string $content = null, array $data = [])
    {
        info('error response, ApiException: '.var_export($content, true));
        throw new ErrorException($data['message'] ?? $data['exception'] ?? 'Unknown api error', $data['code'] ?? 0);
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
            'langTag' => current_lang_tag(),
            'timezone' => Cookie::get('timezone') ?: ConfigHelper::fresnsConfigByItemKey('default_timezone'),
            'aid' => Cookie::get('fs_aid') ?? null,
            'uid' => Cookie::get('fs_uid') ?? null,
            'token' => Cookie::get('fs_uid_token') ?? Cookie::get('fs_aid_token') ?? null,
            'deviceInfo' => json_encode(AppUtility::getDeviceInfo()),
        ];
        $headers['sign'] = SignHelper::makeSign($headers, $appSecret);

        return $headers;
    }
}

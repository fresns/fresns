<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Traits;

use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Utilities\ConfigUtility;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function success($data = null, $message = 'success', $code = 0, $headers = [])
    {
        if (is_string($data)) {
            $code = $message;
            $message = $data;
            $data = null;
        }

        // paginate data
        $meta = [];
        $paginate = [];
        if (isset($data['data']) && isset($data['paginate'])) {
            extract($data);
        }

        $message = ConfigUtility::getCodeMessage($code, null, \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag()));

        $data = $data ?: null;
        $fresnsResponse = compact('code', 'message', 'data') + array_filter(compact('paginate'));

        return \response(
            \json_encode($fresnsResponse, \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT),
            Response::HTTP_OK,
            array_merge([
                'Fresns-Version' => AppHelper::VERSION,
                'Fresns-Api' => 'v2',
                'Fresns-Author' => 'Jarvis Tang',
                'Content-Type' => 'application/json',
            ], $headers)
        );
    }

    public function warning(int $code, ?string $message = 'unknown warning', array|object $data = null)
    {
        $data = [
            'paginate' => [
                'total' => 0,
                'pageSize' => 0,
                'currentPage' => 1,
                'lastPage' => 1,
            ],
            'list' => [],
        ];

        $message = ConfigUtility::getCodeMessage($code, null, \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag())) ?? 'unknown warning';

        return $this->success($data, $message);
    }

    public function failure($code = 30000, $message = 'unknown error', $data = null, $headers = [])
    {
        $message = ConfigUtility::getCodeMessage($code, null, \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag()));

        if (! \request()->wantsJson()) {
            $message = \json_encode(compact('code', 'message', 'data'), \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_PRETTY_PRINT);
            if (! array_key_exists($code, Response::$statusTexts)) {
                $code = 200;
            }

            return \response(
                $message,
                $code,
                array_merge([
                    'Fresns-Version' => AppHelper::VERSION,
                    'Fresns-Api' => 'v2',
                    'Fresns-Author' => 'Jarvis Tang',
                    'Content-Type' => 'application/json',
                ], $headers)
            );
        }

        return $this->success($data, $message ?: 'unknown error', $code ?: 3e4, $headers);
    }

    public function fresnsPaginate($items, $total, $pageSize = 15)
    {
        $paginate = new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $pageSize,
            currentPage: \request('page'),
        );

        $paginate->withPath('/'.\request()->path())->withQueryString();

        return $this->paginate($paginate);
    }

    public function paginate(LengthAwarePaginator $paginate, ?callable $callable = null)
    {
        return $this->success([
            'paginate' => [
                'total' => $paginate->total(),
                'pageSize' => $paginate->perPage(),
                'currentPage' => $paginate->currentPage(),
                'lastPage' => $paginate->lastPage(),
            ],
            'list' => array_map(function ($item) use ($callable) {
                if ($callable) {
                    return $callable($item) ?? $item;
                }

                return $item;
            },
            $paginate->items()),
        ]);
    }
}

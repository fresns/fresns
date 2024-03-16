<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Traits;

use App\Helpers\AppHelper;
use App\Utilities\ConfigUtility;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function success(mixed $data = null, ?string $message = null, ?int $code = 0, ?array $headers = [])
    {
        $message = $message ?: ConfigUtility::getCodeMessage($code, 'Fresns', AppHelper::getLangTag());

        $newHeaders = array_merge([
            'Fresns-Version' => AppHelper::VERSION,
            'Fresns-Api' => 'v1',
            'Fresns-Author' => 'Jevan Tang',
            'Content-Type' => 'application/json',
        ], $headers);

        return response()->json([
            'code' => $code,
            'message' => $message,
            'data' => $data,
        ], Response::HTTP_OK, $newHeaders);
    }

    public function warning(int $code, ?string $message = null, mixed $data = null)
    {
        $data = [
            'pagination' => [
                'total' => 0,
                'pageSize' => 15,
                'currentPage' => 1,
                'lastPage' => 1,
            ],
            'list' => [],
        ];

        $message = ConfigUtility::getCodeMessage($code, 'Fresns', AppHelper::getLangTag()) ?? 'Unknown Warning';
        $newMessage = "[{$code}] {$message}";

        return $this->success($data, $newMessage);
    }

    public function failure($code, ?string $message = null, mixed $data = null, ?array $headers = [])
    {
        return $this->success($data, $message, $code, $headers);
    }

    public function fresnsPaginate($items, $total, $pageSize = 15)
    {
        $paginate = new LengthAwarePaginator(
            items: $items,
            total: $total,
            perPage: $pageSize ?: 15,
            currentPage: request('page'),
        );

        $paginate->withPath('/'.request()->path())->withQueryString();

        return $this->paginate($paginate);
    }

    public function paginate(LengthAwarePaginator $paginate)
    {
        return $this->success([
            'pagination' => [
                'total' => $paginate->total(),
                'pageSize' => $paginate->perPage(),
                'currentPage' => $paginate->currentPage(),
                'lastPage' => $paginate->lastPage(),
            ],
            'list' => $paginate->items(),
        ]);
    }
}

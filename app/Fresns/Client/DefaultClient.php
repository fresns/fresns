<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Client;

use Psr\Http\Message\ResponseInterface;

trait DefaultClient
{
    public function getBaseUri(): ?string
    {
        return null;
    }

    public function handleEmptyResponse(?string $content = null, ?ResponseInterface $response = null)
    {
        throw new \RuntimeException(sprintf('Request fail , response body is ein class %s', static::class), $response->getStatusCode());
    }

    public function isErrorResponse(array $data): bool
    {
        return false;
    }

    public function handleErrorResponse(?string $content = null, array $data = [])
    {
        return null;
    }

    public function hasPaginate(): bool
    {
        return false;
    }

    public function getTotal(): ?int
    {
        return 0;
    }

    public function getPageSize(): ?int
    {
        return 0;
    }

    public function getCurrentPage(): ?int
    {
        return 0;
    }

    public function getLastPage(): ?int
    {
        return 0;
    }

    public function getDataList(): static|array|null
    {
        return null;
    }
}

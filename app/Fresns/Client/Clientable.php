<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Psr7\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Psr\Http\Message\ResponseInterface;

trait Clientable
{
    use Arrayable;

    /** @var Response */
    protected $response;

    protected array $result = [];

    public static function make(): static|Utils|Client
    {
        return new static();
    }

    abstract public function getBaseUri(): ?string;

    public function getOptions()
    {
        return [
            'base_uri' => $this->getBaseUri(),
            'timeout' => 5, // Request 5s timeout
            'http_errors' => false,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];
    }

    public function getHttpClient()
    {
        return new Client($this->getOptions());
    }

    abstract public function handleEmptyResponse(?string $content = null, ?ResponseInterface $response = null);

    abstract public function isErrorResponse(array $data): bool;

    abstract public function handleErrorResponse(?string $content = null, array $data = []);

    abstract public function hasPaginate(): bool;

    abstract public function getTotal(): ?int;

    abstract public function getPageSize(): ?int;

    abstract public function getCurrentPage(): ?int;

    abstract public function getLastPage(): ?int;

    abstract public function getDataList(): static|array|null;

    public function castResponse($response)
    {
        $data = json_decode($content = $response->getBody()->getContents(), true) ?? [];

        if (empty($data)) {
            $this->handleEmptyResponse($content, $response);
        }

        if ($this->isErrorResponse($data)) {
            $this->handleErrorResponse($content, $data);
        }

        return $data;
    }

    public function paginate()
    {
        if (! $this->hasPaginate()) {
            return null;
        }

        $paginate = new LengthAwarePaginator(
            items: $this->getDataList(),
            total: $this->getTotal(),
            perPage: $this->getPageSize(),
            currentPage: $this->getCurrentPage(),
        );

        $paginate
            ->withPath('/'.\request()->path())
            ->withQueryString();

        return $paginate;
    }

    public function __call($method, $args)
    {
        // Asynchronous requests
        if (method_exists(Utils::class, $method)) {
            $results = call_user_func_array([Utils::class, $method], $args);

            if (! is_array($results)) {
                return $results;
            }

            $data = [];
            foreach ($results as $key => $promise) {
                $data[$key] = $this->castResponse($promise);
            }

            $this->setAttributes($data);

            return $this;
        }

        // Synchronization Request
        if (method_exists($this->getHttpClient(), $method)) {
            $this->response = $this->getHttpClient()->$method(...$args);
        }

        // Response results processing
        if ($this->response instanceof Response) {
            $this->result = $this->castResponse($this->response);

            $this->setAttributes($this->result);
        }

        // Return the promise request directly to
        if ($this->response instanceof Promise) {
            return $this->response;
        }

        return $this;
    }
}

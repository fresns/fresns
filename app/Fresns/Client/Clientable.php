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

trait Clientable
{
    /** @var Response */
    protected $response;

    protected array $data = [];

    public static function make(): static|Utils|Client
    {
        return new static();
    }

    public function getBaseUri(): ?string
    {
        return null;
    }

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

    public function castResponse($response)
    {
        $content = $response->getBody()->getContents();

        $data = json_decode($content, true) ?? [];

        return $data;
    }

    public function unwrapRequests(array $requests)
    {
        $results = $this->unwrap($requests);

        if (method_exists($this, 'caseUnwrapRequests')) {
            $results = $this->caseUnwrapRequests($results);
        }

        return $results;
    }

    public function __call(string $method, array $args)
    {
        $result = $this->forwardCall($method, $args);

        if (method_exists($this, 'caseForwardCallResult')) {
            $result = $this->caseForwardCallResult($result);
        }

        return $result;
    }

    public function forwardCall($method, $args)
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

            $this->data = $data;

            return $this->data;
        }
        // Synchronization Request
        elseif (method_exists($this->getHttpClient(), $method)) {
            $this->response = $this->getHttpClient()->$method(...$args);

            // return Promise response
            if ($this->response instanceof Promise) {
                return $this->response;
            }

            // Response results processing
            if ($this->response instanceof Response) {
                $this->data = $this->castResponse($this->response);
            }
        } else {
            throw new \RuntimeException(sprintf('unknown method %s::%s', get_class($this), $method));
        }

        // api data
        return $this->data;
    }
}

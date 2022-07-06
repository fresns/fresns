<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Auth;

use App\Fresns\Web\Helpers\ApiHelper;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Plugins\FresnsEngine\Sdk\Factory;

class AccountGuard implements Guard
{
    /**
     * @var array
     */
    protected $account;

    /**
     * Indicates if the logout method has been called.
     *
     * @var bool
     */
    protected $loggedOut = false;

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Determine if the current account is authenticated. If not, throw an exception.
     *
     * @return array
     *
     * @throws AuthenticationException|GuzzleException
     */
    public function authenticate(): array
    {
        if (! is_null($account = $this->get())) {
            return $account;
        }

        throw new AuthenticationException;
    }

    /**
     * Determine if the guard has a account instance.
     *
     * @return bool
     */
    public function has(): bool
    {
        return ! is_null($this->account);
    }

    /**
     * Determine if the current account is authenticated.
     *
     * @return bool
     *
     * @throws GuzzleException
     */
    public function check(): bool
    {
        try {
            return ! is_null($this->get());
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Determine if the current account is a guest.
     *
     * @return bool
     */
    public function guest(): bool
    {
        return ! $this->check();
    }

    /**
     * Get the ID for the currently authenticated account.
     *
     * @return mixed|null
     *
     * @throws GuzzleException
     */
    public function aid(): string
    {
        if ($this->get()) {
            return $this->get()['detail']['aid'];
        }

        return null;
    }

    /**
     * @param  array  $account
     * @return $this
     */
    public function set(array $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @param  string|null  $key
     * @return array|null
     *
     * @throws GuzzleException
     */
    public function get(?string $key = null)
    {
        if ($this->loggedOut) {
            return null;
        }

        if (! is_null($this->account)) {
            return $key ? Arr::get($this->account, $key) : $this->account;
        }

        $aid = Cookie::get('aid');
        $token = Cookie::get('token');

        if ($aid && $token) {
            try {
                $result = ApiHelper::make()->get('/api/v2/account/detail');
                $this->account = $result['data'];
            } catch (\Throwable $e) {
                $this->logout();
                throw $e;
            }
        }

        return $key ? $this->account[$key] : $this->account;
    }

    /**
     * Account by api login.
     */
    public function logout(): void
    {
        Cookie::queue(Cookie::forget('aid'));
        Cookie::queue(Cookie::forget('uid'));
        Cookie::queue(Cookie::forget('token'));
        Cookie::queue(Cookie::forget('timezone'));
        $this->account = null;
        $this->loggedOut = true;
    }
}

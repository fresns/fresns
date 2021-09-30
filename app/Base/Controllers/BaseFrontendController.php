<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Base\Controllers;

use App\Base\Config\BaseConfig;
use App\Http\Center\Common\LogService;
use App\Servers\AccountServer\UserServer;
use App\Servers\AccountServer\UserServerConfig;
use App\Servers\RpcHelper;
use App\Traits\ApiTrait;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class BaseFrontendController extends Controller
{
    use ApiTrait;
    use AuthenticatesUsers;

    // Login Mode
    public function username()
    {
        $request = request();

        $loginType = $request->input('login_type');

        if (! empty($loginType)) {
            return $loginType;
        }

        return 'login_name';
    }

    public static function checkLoginTest($login_name)
    {
        // Determine the login mode
        if (preg_match(BaseConfig::PHONE_REG, $login_name)) {
            return  BaseConfig::LOGIN_TYPE[BaseConfig::LOGIN_TYPE_PHONE];
        } elseif (filter_var($login_name, FILTER_VALIDATE_EMAIL)) {
            return  BaseConfig::LOGIN_TYPE[BaseConfig::LOGIN_TYPE_EMAIL];
        } else {
            return  BaseConfig::LOGIN_TYPE[BaseConfig::LOGIN_TYPE_NAME];
        }
    }

    // Login
    public function attemptLogin($credentials)
    {
        $request = request();
        LogService::info('The login request information is', $credentials);
        $loginTypeTest = self::checkLoginTest($credentials['login_name']);
        if ($loginTypeTest == 'email') {
            $request->offsetSet('login_type', 'email');
            $request->offsetSet('email', $credentials['login_name']);
            $request->offsetSet('password', $credentials['password']);
        }

        if ($loginTypeTest == 'phone') {
            $request->offsetSet('login_type', 'phone');
            $request->offsetSet('phone', $credentials['login_name']);
            $request->offsetSet('password', $credentials['password']);
        }

        if ($loginTypeTest == 'login_name') {
            $request->offsetSet('login_type', 'login_name');
            $request->offsetSet('login_name', $credentials['login_name']);
            $request->offsetSet('password', $credentials['password']);
        }

        $loginResult = collect(['email', 'phone'])->contains(function ($value) use ($request) {
            $username = $this->username();
            LogService::info('login mode', $username);
            $account = $request->input($this->username());
            $password = $request->input('password');
            $credentials = [
                $value => $account,
                'password' => $password,
            ];
            LogService::info('The login credentials information is', $credentials);

            return $this->guard()->attempt($credentials, $request->filled('remember'));
        });

        LogService::info('Login Results', $loginResult);

        return $loginResult;
    }
}

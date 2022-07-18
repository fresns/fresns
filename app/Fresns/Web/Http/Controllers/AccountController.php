<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Helpers\ApiHelper;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // register
    public function register(Request $request)
    {
        if (fs_account()->check() || fs_user()->check()) {
            return redirect()->intended(fs_route(route('fresns.account.index')));
        }

        return view('account.register');
    }

    // login
    public function login(Request $request)
    {
        if (fs_account()->check() && fs_user()->check()) {
            return redirect()->intended(fs_route(route('fresns.account.index')));
        }

        return view('account.login');
    }

    // logout
    public function logout()
    {
        fs_account()->logout();

        ApiHelper::make()->delete('/api/v2/account/logout');

        return redirect()->intended(fs_route(route('fresns.home')));
    }

    // reset password
    public function resetPassword(Request $request)
    {
        if (fs_account()->check() || fs_user()->check()) {
            return redirect()->intended(fs_route(route('fresns.account.index')));
        }

        return view('account.reset-password');
    }

    // index
    public function index()
    {
        return view('account.index');
    }

    // wallet
    public function wallet()
    {
        return view('account.wallet');
    }

    // users
    public function users()
    {
        return view('account.users');
    }

    // settings
    public function settings()
    {
        return view('account.settings');
    }
}

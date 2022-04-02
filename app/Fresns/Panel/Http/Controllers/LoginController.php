<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        \View::share('langs', config('FsConfig.langs'));
        try {
            $this->redirectTo = route('panel.dashboard');
        } catch (\Exception $e) {
            $this->redirectTo = 'dashboard';
        }
    }

    public function username()
    {
        return 'accountName';
    }

    protected function credentials(Request $request)
    {
        $accountName = $request->accountName;

        filter_var($accountName, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $accountName :
            $credentials['phone'] = $accountName;

        $credentials['password'] = $request->password;

        return $credentials;
    }

    /**
     * Attempt to log the account into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        // check account type
        $account = $this->guard()->getProvider()->retrieveByCredentials($this->credentials($request));
        if (! $account || $account->type != 1) {
            return false;
        }

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        return back()->withErrors([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    public function showLoginForm()
    {
        return view('FsView::auth.login');
    }

    public function loggedOut(Request $request)
    {
        return redirect(route('panel.empty', 'empty'));
    }

    public function emptyPage()
    {
        return view('FsView::auth.empty');
    }
}

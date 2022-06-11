<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Utilities\AppUtility;
use App\Utilities\ConfigUtility;
use Browser;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $loginLimit = false;

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
            $result = false;
        } else {
            $result = $this->guard()->attempt(
                $this->credentials($request), $request->filled('remember')
            );
        }

        // login session log
        if ($account) {
            $loginCount = ConfigUtility::getLoginErrorCount($account->id);

            if ($loginCount >= 5) {
                $this->loginLimit = true;

                return false;
            }

            $langTag = \request()->header('langTag', config('app.locale'));
            $deviceInfo = AppUtility::getDeviceInfo();
            $wordBody = [
                'type' => 2,
                'pluginUnikey' => 'Fresns',
                'platformId' => Browser::isMobile() ? 3 : 2,
                'version' => AppHelper::VERSION,
                'langTag' => $langTag,
                'aid' => (string) $account->aid, // aid by account number
                'uid' => null,
                'objectName' => self::class,
                'objectAction' => 'Panel Login',
                'objectResult' => $result ? 3 : 2, // login success or failure
                'objectOrderId' => null,
                'deviceInfo' => json_encode($deviceInfo),
                'deviceToken' => null,
                'moreJson' => null,
            ];
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($wordBody);
        }

        return $result;
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
        $error = trans('auth.failed');
        if ($this->loginLimit) {
            $error = trans('FsLang::tips.account_login_limit');
        }

        return back()->withErrors([
            $this->username() => [$error],
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

<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Config;
use App\Models\SessionLog;
use App\Utilities\ConfigUtility;
use Browser;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

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
     */
    protected function attemptLogin(Request $request): bool
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

            $langTag = \request()->header('X-Fresns-Client-Lang-Tag', config('app.locale'));

            $wordBody = [
                'type' => SessionLog::TYPE_LOGIN_PANEL,
                'fskey' => 'Fresns',
                'appId' => null,
                'platformId' => Browser::isMobile() ? 3 : 2,
                'version' => AppHelper::VERSION,
                'langTag' => $langTag,
                'aid' => (string) $account->aid,
                'uid' => null,
                'actionName' => self::class,
                'actionDesc' => 'Panel Login',
                'actionState' => $result ? SessionLog::STATE_SUCCESS : SessionLog::STATE_FAILURE,
                'actionId' => null,
                'deviceInfo' => AppHelper::getDeviceInfo(),
                'deviceToken' => null,
                'loginToken' => null,
                'moreInfo' => null,
            ];
            \FresnsCmdWord::plugin('Fresns')->createSessionLog($wordBody);
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
        $panelLang = \request()->cookie('fresns_panel_locale');
        if (empty($panelLang)) {
            Cookie::queue(Cookie::forever('fresns_panel_locale', config('app.locale'), '/'));
        }

        $versionMd5 = AppHelper::VERSION_MD5_16BIT;

        return view('FsView::auth.login', compact('versionMd5'));
    }

    public function loggedOut(Request $request)
    {
        return redirect(route('panel.empty', 'empty'));
    }

    public function emptyPage()
    {
        // site home url
        $siteUrlConfig = Config::where('item_key', 'site_url')->first();
        $siteUrl = $siteUrlConfig->item_value ?? '/';

        $versionMd5 = AppHelper::VERSION_MD5_16BIT;

        return view('FsView::auth.empty', compact('siteUrl', 'versionMd5'));
    }
}

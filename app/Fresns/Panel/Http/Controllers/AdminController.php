<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\StoreAdminRequest;
use App\Helpers\AppHelper;
use App\Models\Account;
use App\Models\App;
use App\Models\SessionLog;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Account::ofAdmin()->get();
        $isFounder = self::isFounder();
        $upgradeCount = App::where('is_upgrade', true)->count();

        return view('FsView::dashboard.admins', compact('admins', 'isFounder', 'upgradeCount'));
    }

    public function store(StoreAdminRequest $request)
    {
        $isFounder = self::isFounder();
        if (! $isFounder) {
            return back()->with('failure', __('FsLang::tips.requestFailure'));
        }

        $accountName = $request->accountName;

        filter_var($accountName, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $accountName :
            $credentials['phone'] = $accountName;

        $admin = Account::where($credentials)->isEnabled()->first();

        if (! $admin) {
            return back()->with('failure', __('FsLang::tips.account_not_found'));
        }

        $admin->type = Account::TYPE_SYSTEM_ADMIN;
        $admin->save();

        $this->createAdminSessionLog($admin, SessionLog::TYPE_PANEL_ADMIN_ADD, 'Panel Admin Add');

        return $this->createSuccess();
    }

    public function destroy(Account $admin)
    {
        $isFounder = self::isFounder();
        if (! $isFounder) {
            return back()->with('failure', __('FsLang::tips.requestFailure'));
        }

        $admin->type = Account::TYPE_GENERAL_ACCOUNT;
        $admin->save();

        $this->createAdminSessionLog($admin, SessionLog::TYPE_PANEL_ADMIN_REMOVE, 'Panel Admin Remove');

        return $this->deleteSuccess();
    }

    public static function isFounder()
    {
        $founder = config('app.founder');
        $account = Auth::user();

        if (! $account || is_null($founder) || $founder === '') {
            return false;
        }

        return (string) $account->id === (string) $founder || (string) $account->aid === (string) $founder;
    }

    protected function createAdminSessionLog(Account $admin, int $type, string $actionDesc): void
    {
        $account = Auth::user();

        if (! $account) {
            return;
        }

        SessionLog::create([
            'type' => $type,
            'app_fskey' => 'Fresns',
            'app_id' => null,
            'platform_id' => 2,
            'version' => AppHelper::VERSION,
            'lang_tag' => request()->header('X-Fresns-Client-Lang-Tag', config('app.locale')),
            'action_name' => self::class,
            'action_desc' => $actionDesc,
            'action_state' => SessionLog::STATE_SUCCESS,
            'action_id' => $admin->id,
            'account_id' => $account->id,
            'user_id' => null,
            'device_info' => AppHelper::getDeviceInfo(),
            'device_token' => null,
            'login_token' => null,
            'more_info' => [
                'adminId' => $admin->id,
                'adminAid' => $admin->aid,
                'adminEmail' => $admin->email,
                'adminPhone' => $admin->phone,
            ],
        ]);
    }
}

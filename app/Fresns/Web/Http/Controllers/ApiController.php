<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Controllers;

use App\Fresns\Web\Helpers\ApiHelper;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;

class ApiController extends Controller
{
    // url sign
    public function urlSign()
    {
        $headers = Arr::except(ApiHelper::getHeaders(), ['Accept']);

        return urlencode(base64_encode(json_encode($headers)));
    }

    // send verify code
    public function sendVerifyCode(Request $request)
    {
    }

    // download link
    public function downloadLink(Request $request)
    {
    }

    // account register
    public function accountRegister(Request $request)
    {
        return ApiHelper::make()->post('/api/v2/account/register', [
            'json' => [
                'type' => $request->type,
                'account' => $request->account,
                'countryCode' => $request->countryCode ?? null,
                'verifyCode' => $request->verifyCode,
                'password' => $request->password,
                'nickname' => $request->nickname,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);
    }

    // account login
    public function accountLogin(Request $request)
    {
        $result = ApiHelper::make()->post('/api/v2/account/login', [
            'json' => [
                'type' => $request->type,
                'account' => $request->{$request->type},
                'countryCode' => $request->countryCode ?? null,
                'password' => $request->password ?? null,
                'verifyCode' => $request->verifyCode ?? null,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);

        // api data
        $data = $result['data'];

        // 账号登录
        Cookie::queue('fs_aid', $data['detail']['aid']);
        Cookie::queue('fs_aid_token', $data['sessionToken']['token']);

        // 用户数量
        $users = $data['detail']['users']->toArray();
        $userCount = count($users);

        // 只有一个用户，用户没有密码
        if ($userCount == 1) {
            $user = $users[0];

            if ($user['hasPassword']) {
                // 用户有密码的操作，自动弹出输入密码
                // 弹窗逻辑写在 header.blade.php
                return redirect()->intended(fs_route(route('fresns.account.login')));
            } else {
                // 用户没有有密码
                $userResult = ApiHelper::make()->post('/api/v2/user/auth', [
                    'json' => [
                        'uidOrUsername' => $user['uid'],
                        'password' => null,
                        'deviceToken' => $request->deviceToken ?? null,
                    ],
                ]);

                Cookie::queue('fs_uid', $userResult['data.detail.uid']);
                Cookie::queue('fs_uid_token', $userResult['data.sessionToken.token']);
                Cookie::queue('timezone', $userResult['data.detail.timezone']);

                return redirect()->intended(fs_route(route('fresns.account.index')));
            }
        } elseif ($userCount > 1) {
            // 有 2 个以上用户的操作，自动弹出选择用户
            // 弹窗逻辑写在 header.blade.php
            return redirect()->intended(fs_route(route('fresns.account.login')));
        }

        return back()->with([
            'failure' => ConfigUtility::getCodeMessage(31602, 'Fresns', current_lang_tag()),
        ]);
    }

    // account reset password
    public function resetPassword(Request $request)
    {
    }

    // user auth
    public function userAuth(Request $request)
    {
        $result = ApiHelper::make()->post('/api/v2/user/auth', [
            'json' => [
                'uidOrUsername' => $request->uidOrUsername,
                'password' => $request->password ?? null,
                'deviceToken' => $request->deviceToken ?? null,
            ],
        ]);

        Cookie::queue('fs_uid', $result['data.detail.uid']);
        Cookie::queue('fs_uid_token', $result['data.sessionToken.token']);
        Cookie::queue('timezone', $result['data.detail.timezone']);

        return redirect()->intended(fs_route(route('fresns.account.index')));
    }

    // user mark
    public function userMark(Request $request)
    {
    }

    // user mark note
    public function userMarkNote(Request $request)
    {
    }

    // post delete
    public function postDelete(string $pid)
    {
        return ApiHelper::make()->delete("/api/v2/post/{$pid}");
    }

    // comment delete
    public function commentDelete(string $cid)
    {
        return ApiHelper::make()->delete("/api/v2/comment/{$cid}");
    }
}

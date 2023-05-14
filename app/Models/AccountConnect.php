<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class AccountConnect extends Model
{
    use Traits\IsEnabledTrait;

    const CONNECT_OTHER = 1;
    const CONNECT_FRESNS = 2;
    const CONNECT_SSO = 3;
    const CONNECT_GITHUB = 4;
    const CONNECT_GITLAB = 5;
    const CONNECT_BITBUCKET = 6;
    const CONNECT_GOOGLE = 7;
    const CONNECT_FACEBOOK = 8;
    const CONNECT_INSTAGRAM = 9;
    const CONNECT_TWITTER = 10;
    const CONNECT_DISCORD = 11;
    const CONNECT_TELEGRAM = 12;
    const CONNECT_APPLE = 13;
    const CONNECT_MICROSOFT = 14;
    const CONNECT_LINKEDIN = 15;
    const CONNECT_PAYPAL = 16;
    const CONNECT_SLACK = 17;
    const CONNECT_NETLIFY = 18;
    const CONNECT_LINE = 19;
    const CONNECT_KAKAOTALK = 20;
    const CONNECT_LARK = 21;
    const CONNECT_STEAM = 22;
    const CONNECT_WECHAT_OPEN_PLATFORM = 23;
    const CONNECT_WECHAT_OFFICIAL_ACCOUNT = 24;
    const CONNECT_WECHAT_MINI_PROGRAM = 25;
    const CONNECT_WECHAT_MOBILE_APPLICATION = 26;
    const CONNECT_WECHAT_WEBSITE_APPLICATION = 27;
    const CONNECT_WECOM = 28;
    const CONNECT_QQ = 29;
    const CONNECT_GITEE = 30;
    const CONNECT_WEIBO = 31;
    const CONNECT_ALIPAY = 32;
    const CONNECT_BYTEDANCE = 33;

    protected $casts = [
        'more_json' => 'json',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}

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

    const PLATFORM_OTHER = 1;
    const PLATFORM_FRESNS = 2;
    const PLATFORM_SSO = 3;
    const PLATFORM_GITHUB = 4;
    const PLATFORM_GITLAB = 5;
    const PLATFORM_BITBUCKET = 6;
    const PLATFORM_GOOGLE = 7;
    const PLATFORM_FACEBOOK = 8;
    const PLATFORM_INSTAGRAM = 9;
    const PLATFORM_TWITTER = 10;
    const PLATFORM_DISCORD = 11;
    const PLATFORM_TELEGRAM = 12;
    const PLATFORM_APPLE = 13;
    const PLATFORM_MICROSOFT = 14;
    const PLATFORM_LINKEDIN = 15;
    const PLATFORM_PAYPAL = 16;
    const PLATFORM_SLACK = 17;
    const PLATFORM_NETLIFY = 18;
    const PLATFORM_LINE = 19;
    const PLATFORM_KAKAOTALK = 20;
    const PLATFORM_LARK = 21;
    const PLATFORM_STEAM = 22;
    const PLATFORM_WECHAT_OPEN_PLATFORM = 23;
    const PLATFORM_WECHAT_OFFICIAL_ACCOUNT = 24;
    const PLATFORM_WECHAT_MINI_PROGRAM = 25;
    const PLATFORM_WECHAT_MOBILE_APPLICATION = 26;
    const PLATFORM_WECHAT_WEBSITE_APPLICATION = 27;
    const PLATFORM_WECOM = 28;
    const PLATFORM_QQ_OPEN_PLATFORM = 29;
    const PLATFORM_QQ_MINI_PROGRAM = 30;
    const PLATFORM_QQ_MOBILE_APPLICATION = 31;
    const PLATFORM_QQ_WEBSITE_APPLICATION = 32;
    const PLATFORM_GITEE = 33;
    const PLATFORM_WEIBO = 34;
    const PLATFORM_ALIPAY = 35;
    const PLATFORM_BYTEDANCE = 36;

    protected $casts = [
        'more_info' => 'json',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }
}

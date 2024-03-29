<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use App\Helpers\ConfigHelper;
use App\Helpers\StrHelper;

class User extends Model
{
    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;
    use Traits\UserServiceTrait;

    const GENDER_UNKNOWN = 1;
    const GENDER_MALE = 2;
    const GENDER_FEMALE = 3;
    const GENDER_CUSTOM = 4;

    const GENDER_PRONOUN_SHE = 1;
    const GENDER_PRONOUN_HE = 2;
    const GENDER_PRONOUN_THEY = 3;

    const BIRTHDAY_DISPLAY_FULL = 1;
    const BIRTHDAY_DISPLAY_YEAR = 2;
    const BIRTHDAY_DISPLAY_MONTH_AND_DAY = 3;
    const BIRTHDAY_DISPLAY_PRIVATE = 4;

    const POLICY_EVERYONE = 1;
    const POLICY_PEOPLE_YOU_FOLLOW = 2;
    const POLICY_PEOPLE_YOU_FOLLOW_OR_VERIFIED = 3;
    const POLICY_NO_ONE_IS_ALLOWED = 4;
    const POLICY_ONLY_USERS_YOU_MENTION = 5;

    protected $casts = [
        'more_info' => 'json',
    ];

    protected $dates = [
        'verified_at',
        'expired_at',
        'last_login_at',
        'last_activity_at',
        'last_post_at',
        'last_comment_at',
        'last_username_at',
        'last_nickname_at',
        'wait_delete_at',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $digit = ConfigHelper::fresnsConfigByItemKey('user_uid_digit');

            $model->uid = $model->uid ?? static::generateUid($digit);
        });
    }

    public function getFsidKey()
    {
        return 'username';
    }

    // generate uid
    public static function generateUid(int $digit): int
    {
        $uid = StrHelper::generateDigital($digit);

        $checkUid = static::where('uid', $uid)->first();

        if (! $checkUid) {
            return $uid;
        } else {
            $newUid = $uid + 1;
            $checkNewUid = static::where('uid', $uid)->first();
            if (! $checkNewUid) {
                return $newUid;
            }
        }

        return static::generateUid($digit + 1);
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id', 'id');
    }

    public function stat()
    {
        return $this->hasOne(UserStat::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->wherePivotNull('deleted_at');
    }

    public function getMainRoleAttribute()
    {
        return $this->roles()->wherePivot('is_main', true)->wherePivotNull('expired_at')->orWherePivot('expired_at', '>=', now())->first();
    }
}

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
    use Traits\UserServiceTrait;
    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;

    const GENDER_UNKNOWN = 1;
    const GENDER_MALE = 2;
    const GENDER_FEMALE = 3;

    protected $casts = [
        'more_info' => 'json',
    ];

    protected $dates = [
        'birthday',
        'verified_at',
        'expired_at',
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

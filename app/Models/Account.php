<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Account extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Traits\AccountServiceTrait;
    use Traits\DataChangeNotifyTrait;
    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;
    use Traits\LangNameTrait;
    use Traits\LangDescriptionTrait;

    const TYPE_SYSTEM_ADMIN = 1;
    const TYPE_GENERAL_ADMIN = 2;
    const TYPE_GENERAL_ACCOUNT = 3;

    const ACT_TYPE_EMAIL = 1;
    const ACT_TYPE_PHONE = 2;
    const ACT_TYPE_CONNECT = 3;

    protected $guarded = [];

    protected $dates = [
        'last_login_at',
        'verify_at',
        'wait_delete_at',
    ];

    protected $hidden = [
        'password',
    ];

    public function getFsidKey()
    {
        return 'aid';
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    public function wallet()
    {
        return $this->hasOne(AccountWallet::class);
    }

    public function walletLogs()
    {
        return $this->hasMany(AccountWalletLog::class);
    }

    public function connects()
    {
        return $this->hasMany(AccountConnect::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeOfAdmin($query)
    {
        return $query->where('type', Account::TYPE_SYSTEM_ADMIN);
    }

    public function getSecretPurePhoneAttribute(): string
    {
        if (! $this->pure_phone) {
            return '';
        }

        return Str::mask($this->pure_phone, '*', -8, 4);
    }

    public function getSecretEmailAttribute(): string
    {
        if (! $this->email) {
            return '';
        }

        [$prefix, $end] = explode('@', $this->email);
        $len = ceil(strlen($prefix) / 2);

        return Str::mask($prefix, '*', -1 * $len, $len).'@'.$end;
    }

    public function isAdmin()
    {
        return $this->type == Account::TYPE_SYSTEM_ADMIN;
    }
}

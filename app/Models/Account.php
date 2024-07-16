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

class Account extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;
    use Traits\AccountServiceTrait;
    use Traits\DataChangeNotifyTrait;
    use Traits\FsidTrait;
    use Traits\IsEnabledTrait;

    const TYPE_SYSTEM_ADMIN = 1;
    const TYPE_GENERAL_ADMIN = 2;
    const TYPE_GENERAL_ACCOUNT = 3;

    const CREATE_TYPE_AID = 1;
    const CREATE_TYPE_EMAIL = 2;
    const CREATE_TYPE_PHONE = 3;
    const CREATE_TYPE_CONNECT = 4;

    const VERIFY_TYPE_AUTO = 1;
    const VERIFY_TYPE_AID = 2;
    const VERIFY_TYPE_EMAIL = 3;
    const VERIFY_TYPE_PHONE = 4;
    const VERIFY_TYPE_CONNECT = 5;

    protected $guarded = [];

    protected $dates = [
        'birthday',
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

    public function isAdmin()
    {
        return $this->type == Account::TYPE_SYSTEM_ADMIN;
    }
}

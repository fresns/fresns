<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
    use Traits\IsEnableTrait;
    use Traits\FsidTrait;
    use Traits\LangNameTrait;
    use Traits\LangDescriptionTrait;

    protected $dates = [
        'last_login_at',
        'verify_at',
        'wait_delete_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
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
        return $query->where('type', 1);
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
        return $this->type == 1;
    }
}

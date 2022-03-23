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

class Account extends Authenticatable
{
    use SoftDeletes;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'phone', 'email',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function scopeOfAdmin($query)
    {
        return $query->where('type', 1);
    }

    public function getSecretPurePhoneAttribute(): string
    {
        if (! $this->pure_phone) {
            return '';
        }

        return \Str::mask($this->pure_phone, '*', -8, 4);
    }

    public function getSecretEmailAttribute(): string
    {
        if (! $this->email) {
            return '';
        }

        [$prefix, $end] = explode('@', $this->email);
        $len = ceil(strlen($prefix) / 2);

        return \Str::mask($prefix, '*', -1 * $len, $len).'@'.$end;
    }

    public function isAdmin()
    {
        return $this->type == 1;
    }
}

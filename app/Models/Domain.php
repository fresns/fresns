<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Domain extends Model
{
    use Traits\IsEnableTrait;

    public function links()
    {
        return $this->hasMany(DomainLink::class);
    }
}

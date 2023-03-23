<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class DomainLink extends Model
{
    use Traits\IsEnableTrait;

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_id', 'id');
    }
}

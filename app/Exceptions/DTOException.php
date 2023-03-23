<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Exceptions;

use App\Fresns\Api\Traits\ApiResponseTrait;

class DTOException extends \Exception
{
    use ApiResponseTrait;

    public function render()
    {
        // if (!\request()->wantJsons()) {
        //     return view('error.30000', $this);
        // }

        return $this->failure(30000, $this->getMessage());
    }
}

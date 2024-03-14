<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Exceptions;

use App\Fresns\Api\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use App\Utilities\ConfigUtility;

class ResponseException extends \Exception
{
    use ApiResponseTrait;

    protected $data;

    public function __construct(int $code, ?string $fskey = '', mixed $data = null)
    {
        $message = $this->getCodeMessage($code, $fskey);
        $this->data = $data;

        parent::__construct($message, $code);
    }

    public function getCodeMessage(int $code, ?string $fskey = '')
    {
        return ConfigUtility::getCodeMessage($code, $fskey, AppHelper::getLangTag());
    }

    public function render()
    {
        return $this->failure($this->getCode(), $this->getMessage(), $this->data);
    }
}

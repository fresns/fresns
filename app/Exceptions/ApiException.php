<?php

namespace App\Exceptions;

use App\Utilities\ConfigUtility;
use App\Fresns\Api\Traits\ApiResponseTrait;

class ApiException extends \Exception
{
    use ApiResponseTrait;

    public function __construct(int $code, ?string $unikey = '')
    {
        $message = $this->getCodeMessage($code, $unikey);

        parent::__construct($message, $code);
    }

    public function getCodeMessage(int $code, ?string $unikey = '')
    {
        return ConfigUtility::getCodeMessage($code, $unikey, \request()->header('langTag'));
    }

    public function render()
    {
        return $this->failure($this->getCode(), $this->getMessage());
    }
}

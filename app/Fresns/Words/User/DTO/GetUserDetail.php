<?php

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

/**
 * Class GetUserDetail
 * @package App
 */
class GetUserDetail extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required_without:username'],
            'username' => ['string', 'required_without:uid']
        ];
    }
}

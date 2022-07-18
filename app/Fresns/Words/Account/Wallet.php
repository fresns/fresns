<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\WalletDecreaseDTO;
use App\Fresns\Words\Account\DTO\WalletIncreaseDTO;
use App\Helpers\PrimaryHelper;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Wallet
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function walletIncrease($wordBody)
    {
        $dtoWordBody = new WalletIncreaseDTO($wordBody);
        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody['uid']);

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return array
     */
    public function walletDecrease($wordBody)
    {
        $dtoWordBody = new WalletDecreaseDTO($wordBody);
        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody['uid']);

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return array
     */
    public function walletRevoke($wordBody)
    {
        $dtoWordBody = new WalletDecreaseDTO($wordBody);
        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody['uid']);

        return $this->success();
    }

    // wallet amount
    public function amountIncrement(int $accountId, int $userId, float $amountTotal, float $systemFee, ?int $objectAccountId = null, ?int $objectUserId = null)
    {

    }

    public function amountDecrement(int $accountId, int $userId, float $amountTotal, float $systemFee, ?int $objectAccountId = null, ?int $objectUserId = null)
    {

    }
}

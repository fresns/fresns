<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe;

use App\Helpers\PluginHelper;
use App\Models\Plugin;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class SubscribeService
{
    use CmdWordResponseTrait;

    public function addSubscribeItem(array $wordBody)
    {
        $subscribe = Subscribe::make($wordBody);

        // Subscribe already exists
        if ($subscribe->ensureSubscribeExists()) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_RESP_ERROR)::throw("fskey {$subscribe->getFskey()} already subscribed table {$subscribe->getSubTableName()}");
        }

        // Add subscribe
        $subscribe->save();

        return $this->success();
    }

    public function deleteSubscribeItem(array $wordBody)
    {
        $subscribe = Subscribe::make($wordBody);

        if ($subscribe->ensureSubscribeNotExists()) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_RESP_ERROR)::throw("fskey {$subscribe->getFskey()} unsubscribed table {$subscribe->getSubTableName()}");
        }

        $subscribe->remove();

        return $this->success();
    }

    public function notifyDataChange(object $event)
    {
        $event = TableDataChangeEvent::make($event);
        $subscribe = Subscribe::make();

        $subscribe->getTableDataChangeSubscribes()->map(function ($subscribe) use ($event) {
            $pluginStatus = Plugin::where('fskey', $subscribe->getFskey())->isEnable()->first();

            if ($event->ensureSubscribedByThisTable($subscribe) && $pluginStatus) {
                $event->notify($subscribe);
            }
        });
    }

    public function notifyUserActivate(object $event)
    {
        $event = UserActivateEvent::make($event);
        $subscribe = Subscribe::make();

        $subscribe->getUserActivateSubscribes()->map(function ($subscribe) use ($event) {
            $pluginStatus = Plugin::where('fskey', $subscribe->getFskey())->isEnable()->first();

            if ($pluginStatus) {
                $event->notify($subscribe);
            }
        });
    }

    public static function notifyAccountAndUserLogin(int $accountId, array $accountToken, array $accountDetail, ?int $userId = null, ?array $userToken = null, ?array $userDetail = null)
    {
        $subscribeItems = PluginHelper::fresnsPluginSubscribeItems(Subscribe::TYPE_ACCOUNT_AND_USER_LOGIN);
        if (empty($subscribeItems)) {
            return;
        }

        $wordBody = [
            'primaryId' => [
                'accountId' => $accountId,
                'userId' => $userId,
            ],
            'accountToken' => $accountToken,
            'accountDetail' => $accountDetail,
            'userToken' => $userToken,
            'userDetail' => $userDetail,
        ];

        foreach ($subscribeItems as $item) {
            $plugin = Plugin::where('fskey', $item['fskey'])->isEnable()->first();
            if (empty($plugin)) {
                continue;
            }

            \FresnsCmdWord::plugin($item['fskey'])->$item['cmdWord']($wordBody);
        }
    }
}

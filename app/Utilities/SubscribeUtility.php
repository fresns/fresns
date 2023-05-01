<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use App\Helpers\StrHelper;

class SubscribeUtility
{
    const TYPE_TABLE_DATA_CHANGE = 1;
    const TYPE_USER_ACTIVITY = 2;
    const TYPE_ACCOUNT_AND_USER_LOGIN = 3;

    const CHANGE_TYPE_CREATED = 'created';
    const CHANGE_TYPE_UPDATED = 'updated';
    const CHANGE_TYPE_DELETED = 'deleted';

    // get subscribe items
    public static function getSubscribeItems(?int $type = null): array
    {
        $subscribeItems = ConfigHelper::fresnsConfigByItemKey('subscribe_items') ?? [];

        if (empty($subscribeItems)) {
            return [];
        }

        if (empty($type)) {
            return $subscribeItems;
        }

        $filtered = array_filter($subscribeItems, function ($item) use ($type) {
            return $item['type'] == $type;
        });

        return array_values($filtered);
    }

    // notifyDataChange
    public static function notifyDataChange(mixed $tableName, int $primaryId, string $changeType): void
    {
        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_TABLE_DATA_CHANGE);
        if (empty($subscribeItems)) {
            return;
        }

        $subTableName = null;
        try {
            if ($tableName) {
                $subTableName = StrHelper::qualifyTableName($tableName);
            }
        } catch (\Exception $e) {
        }

        if (empty($subTableName)) {
            return;
        }

        $wordBody = [
            'tableName' => $subTableName,
            'primaryId' => $primaryId,
            'changeType' => $changeType,
        ];

        foreach ($subscribeItems as $item) {
            try {
                $fskey = $item['fskey'];
                $cmdWord = $item['cmdWord'];

                \FresnsCmdWord::plugin($fskey)->$cmdWord($wordBody);
            } catch (\Exception $e) {
            }
        }
    }

    // notifyUserActivity
    public static function notifyUserActivity(string $route, string $uri, array $headers, mixed $body): void
    {
        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_USER_ACTIVITY);
        if (empty($subscribeItems)) {
            return;
        }

        $wordBody = [
            'route' => $route,
            'uri' => $uri,
            'headers' => $headers,
            'body' => $body,
        ];

        foreach ($subscribeItems as $item) {
            try {
                $fskey = $item['fskey'];
                $cmdWord = $item['cmdWord'];

                \FresnsCmdWord::plugin($fskey)->$cmdWord($wordBody);
            } catch (\Exception $e) {
            }
        }
    }

    // notifyAccountAndUserLogin
    public static function notifyAccountAndUserLogin(int $accountId, array $accountToken, array $accountDetail, ?int $userId = null, ?array $userToken = null, ?array $userDetail = null): void
    {
        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_ACCOUNT_AND_USER_LOGIN);
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
            try {
                $fskey = $item['fskey'];
                $cmdWord = $item['cmdWord'];

                \FresnsCmdWord::plugin($fskey)->$cmdWord($wordBody);
            } catch (\Exception $e) {
            }
        }
    }
}

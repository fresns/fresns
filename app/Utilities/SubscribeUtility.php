<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Jobs\NotifyAccountAndUserLogin;
use App\Jobs\NotifyDataChange;
use App\Jobs\NotifyUserActivity;
use App\Jobs\NotifyViewContent;

class SubscribeUtility
{
    const TYPE_TABLE_DATA_CHANGE = 1;
    const TYPE_USER_ACTIVITY = 2;
    const TYPE_ACCOUNT_AND_USER_LOGIN = 3;
    const TYPE_VIEW_CONTENT = 4;

    const CHANGE_TYPE_CREATED = 'created';
    const CHANGE_TYPE_UPDATED = 'updated';
    const CHANGE_TYPE_DELETED = 'deleted';

    const VIEW_TYPE_USER = 'user';
    const VIEW_TYPE_GROUP = 'group';
    const VIEW_TYPE_HASHTAG = 'hashtag';
    const VIEW_TYPE_GEOTAG = 'geotag';
    const VIEW_TYPE_POST = 'post';
    const VIEW_TYPE_COMMENT = 'comment';

    // get subscribe items
    public static function getSubscribeItems(?int $type = null, ?string $subject = null): array
    {
        $subscribeItems = ConfigHelper::fresnsConfigByItemKey('subscribe_items') ?? [];

        if (empty($subscribeItems)) {
            return [];
        }

        if (empty($type)) {
            return $subscribeItems;
        }

        $filtered = array_filter($subscribeItems, function ($item) use ($type, $subject) {
            if ($subject) {
                return $item['type'] == $type && $item['subject'] == $subject;
            }

            return $item['type'] == $type;
        });

        $subArr = array_values($filtered);

        // info('subscribe items', [$type, $subArr]);

        return $subArr;
    }

    // notifyDataChange
    public static function notifyDataChange(mixed $tableName, int $primaryId, string $changeType): void
    {
        NotifyDataChange::dispatch($tableName, $primaryId, $changeType);
    }

    // notifyUserActivity
    public static function notifyUserActivity(): void
    {
        $wordBody = [
            'ip' => request()?->ip(),
            'port' => $_SERVER['REMOTE_PORT'] ?? null,
            'uri' => request()?->getRequestUri(),
            'routeName' => request()?->route()?->getName(),
            'headers' => AppHelper::getHeaders(),
            'body' => request()->except(['file', 'image', 'video', 'audio', 'document']),
        ];

        NotifyUserActivity::dispatch($wordBody);
    }

    // notifyAccountAndUserLogin
    public static function notifyAccountAndUserLogin(int $accountId, array $authToken, array $accountDetail, ?int $userId = null, ?array $userDetail = null): void
    {
        $wordBody = [
            'primaryId' => [
                'accountId' => $accountId,
                'userId' => $userId,
            ],
            'authToken' => $authToken,
            'accountDetail' => $accountDetail,
            'userDetail' => $userDetail,
        ];

        NotifyAccountAndUserLogin::dispatch($wordBody);
    }

    // notifyViewContent
    public static function notifyViewContent(string $type, string $fsid, string $viewType, ?int $authUserId = null): void
    {
        if (! in_array($type, ['user', 'group', 'hashtag', 'geotag', 'post', 'comment']) || ! in_array($viewType, ['list', 'detail'])) {
            return;
        }

        $wordBody = [
            'ip' => request()?->ip(),
            'port' => $_SERVER['REMOTE_PORT'] ?? null,
            'uri' => request()?->getRequestUri(),
            'routeName' => request()?->route()?->getName(),
            'headers' => AppHelper::getHeaders(),
            'type' => $type,
            'fsid' => $fsid,
            'viewType' => $viewType, // list or detail
            'authUserId' => $authUserId,
        ];

        NotifyViewContent::dispatch($wordBody);
    }
}

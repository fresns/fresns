<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Helpers;

use App\Helpers\CacheHelper;
use App\Models\File;
use Illuminate\Support\Facades\Cache;

class DataHelper
{
    // get upload info
    public static function getUploadInfo(?int $usageType = null, ?string $tableName = null, ?string $tableColumn = null, ?int $tableId = null, ?string $tableKey = null)
    {
        $uploadInfo = [
            'image' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'image',
            ],
            'video' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'video',
            ],
            'audio' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'audio',
            ],
            'document' => [
                'usageType' => $usageType,
                'tableName' => $tableName,
                'tableColumn' => $tableColumn,
                'tableId' => $tableId,
                'tableKey' => $tableKey,
                'type' => 'document',
            ],
        ];

        return $uploadInfo;
    }

    // get top list
    public static function getTopList(): array
    {
        $langTag = current_lang_tag();

        $cacheKey = "fresns_web_api_top_list_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);

        $data = Cache::remember($cacheKey, $cacheTime, function () {
            $userQuery = QueryHelper::configToQuery(QueryHelper::TYPE_USER);
            $groupQuery = QueryHelper::configToQuery(QueryHelper::TYPE_GROUP);
            $hashtagQuery = QueryHelper::configToQuery(QueryHelper::TYPE_HASHTAG);
            $postQuery = QueryHelper::configToQuery(QueryHelper::TYPE_POST);
            $commentQuery = QueryHelper::configToQuery(QueryHelper::TYPE_COMMENT);

            $client = ApiHelper::make();

            $results = $client->unwrapRequests([
                'users' => $client->getAsync('/api/v2/user/list', [
                    'query' => $userQuery,
                ]),
                'groups' => $client->getAsync('/api/v2/group/list', [
                    'query' => $groupQuery,
                ]),
                'hashtags' => $client->getAsync('/api/v2/hashtag/list', [
                    'query' => $hashtagQuery,
                ]),
                'posts' => $client->getAsync('/api/v2/post/list', [
                    'query' => $postQuery,
                ]),
                'comments' => $client->getAsync('/api/v2/comment/list', [
                    'query' => $commentQuery,
                ]),
                'stickies' => $client->getAsync('/api/v2/post/list', [
                    'query' => [
                        'stickyState' => 3,
                    ],
                ]),
                'groupTree' => $client->getAsync('/api/v2/group/tree'),
            ]);

            $data['users'] = data_get($results, 'users.data.list', []);
            $data['groups'] = data_get($results, 'groups.data.list', []);
            $data['hashtags'] = data_get($results, 'hashtags.data.list', []);
            $data['posts'] = data_get($results, 'posts.data.list', []);
            $data['comments'] = data_get($results, 'comments.data.list', []);
            $data['stickies'] = data_get($results, 'stickies.data.list', []);
            $data['groupTree'] = data_get($results, 'groupTree.data', []);

            return $data;
        });

        return $data;
    }
}

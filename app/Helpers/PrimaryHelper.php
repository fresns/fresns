<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Account;
use App\Models\Archive;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\Config;
use App\Models\Conversation;
use App\Models\Extend;
use App\Models\File;
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Operation;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\Seo;
use App\Models\SessionKey;
use App\Models\User;

class PrimaryHelper
{
    // get model by fsid
    public static function fresnsModelByFsid(string $modelName, ?string $fsid = null): mixed
    {
        if (empty($fsid)) {
            return null;
        }

        $cacheKey = "fresns_model_{$modelName}_{$fsid}";
        if ($modelName == 'user') {
            $cacheKey = $cacheKey.'_by_fsid';
        }

        $cacheTag = match ($modelName) {
            'config' => 'fresnsConfigs',
            'key' => 'fresnsSystems',
            'account' => 'fresnsAccounts',
            'user' => 'fresnsUsers',
            'group' => 'fresnsGroups',
            'hashtag' => 'fresnsHashtags',
            'geotag' => 'fresnsGeotags',
            'post' => 'fresnsPosts',
            'comment' => 'fresnsComments',
            'file' => 'fresnsFiles',
            'extend' => 'fresnsExtends',
            'archive' => 'fresnsArchives',
            'postLog' => 'fresnsPosts',
            'commentLog' => 'fresnsComments',
            default => 'fresnsModels',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $fresnsModel = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($fresnsModel)) {
            $fresnsModel = null;

            switch ($modelName) {
                case 'config':
                    $fresnsModel = Config::where('item_key', $fsid)->first();
                    break;

                case 'key':
                    $fresnsModel = SessionKey::where('app_id', $fsid)->first();
                    break;

                case 'account':
                    $fresnsModel = Account::withTrashed()->with(['users', 'connects'])->where('aid', $fsid)->first();
                    break;

                case 'user':
                    if (StrHelper::isPureInt($fsid)) {
                        $fresnsModel = User::withTrashed()->where('uid', $fsid)->first();
                    } else {
                        $fresnsModel = User::withTrashed()->where('username', $fsid)->first();
                    }
                    break;

                case 'group':
                    $fresnsModel = Group::withTrashed()->with(['creator', 'admins'])->where('gid', $fsid)->first();
                    break;

                case 'hashtag':
                    $fresnsModel = Hashtag::withTrashed()->where('slug', $fsid)->first();
                    break;

                case 'geotag':
                    $fresnsModel = Geotag::withTrashed()->where('gtid', $fsid)->first();
                    break;

                case 'post':
                    $fresnsModel = Post::withTrashed()->with(['author', 'group', 'hashtags'])->where('pid', $fsid)->first();
                    break;

                case 'comment':
                    $fresnsModel = Comment::withTrashed()->with(['author', 'post', 'hashtags'])->where('cid', $fsid)->first();
                    break;

                case 'file':
                    $fresnsModel = File::withTrashed()->where('fid', $fsid)->first();
                    break;

                case 'extend':
                    $fresnsModel = Extend::withTrashed()->where('eid', $fsid)->first();
                    break;

                case 'archive':
                    $fresnsModel = Archive::withTrashed()->where('code', $fsid)->first();
                    break;

                case 'postLog':
                    $fresnsModel = PostLog::withTrashed()->with(['author', 'group'])->where('hpid', $fsid)->first();
                    break;

                case 'commentLog':
                    $fresnsModel = CommentLog::withTrashed()->with(['author', 'post'])->where('hcid', $fsid)->first();
                    break;
            }

            CacheHelper::put($fresnsModel, $cacheKey, $cacheTag);
        }

        return $fresnsModel;
    }

    // get model by id
    public static function fresnsModelById(string $modelName, ?string $id = null): mixed
    {
        if (empty($id) || $id == 0) {
            return null;
        }

        $cacheKey = "fresns_model_{$modelName}_{$id}";
        $cacheTag = match ($modelName) {
            'key' => 'fresnsSystems',
            'account' => 'fresnsAccounts',
            'user' => 'fresnsUsers',
            'group' => 'fresnsGroups',
            'hashtag' => 'fresnsHashtags',
            'geotag' => 'fresnsGeotags',
            'post' => 'fresnsPosts',
            'comment' => 'fresnsComments',
            'file' => 'fresnsFiles',
            'operation' => 'fresnsOperations',
            'postLog' => 'fresnsPosts',
            'commentLog' => 'fresnsComments',
            default => 'fresnsModels',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $fresnsModel = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($fresnsModel)) {
            $fresnsModel = null;

            switch ($modelName) {
                case 'key':
                    $fresnsModel = SessionKey::where('id', $id)->first();
                    break;

                case 'account':
                    $fresnsModel = Account::withTrashed()->with(['users', 'connects'])->where('id', $id)->first();
                    break;

                case 'user':
                    $fresnsModel = User::withTrashed()->where('id', $id)->first();
                    break;

                case 'group':
                    $fresnsModel = Group::withTrashed()->with(['creator', 'admins'])->where('id', $id)->first();
                    break;

                case 'hashtag':
                    $fresnsModel = Hashtag::withTrashed()->where('id', $id)->first();
                    break;

                case 'geotag':
                    $fresnsModel = Geotag::withTrashed()->where('id', $id)->first();
                    break;

                case 'post':
                    $fresnsModel = Post::withTrashed()->with(['author', 'group', 'hashtags'])->where('id', $id)->first();
                    break;

                case 'comment':
                    $fresnsModel = Comment::withTrashed()->with(['author', 'post', 'hashtags'])->where('id', $id)->first();
                    break;

                case 'file':
                    $fresnsModel = File::withTrashed()->where('id', $id)->first();
                    break;

                case 'operation':
                    $fresnsModel = Operation::withTrashed()->where('id', $id)->first();
                    break;

                case 'postLog':
                    $fresnsModel = PostLog::withTrashed()->with(['author', 'group'])->where('id', $id)->first();
                    break;

                case 'commentLog':
                    $fresnsModel = CommentLog::withTrashed()->with(['author', 'post'])->where('id', $id)->first();
                    break;
            }

            CacheHelper::put($fresnsModel, $cacheKey, $cacheTag);
        }

        return $fresnsModel;
    }

    // get seo info model
    public static function fresnsModelSeo(int $usageType, int $usageId): mixed
    {
        $cacheKey = "fresns_model_seo_{$usageType}_{$usageId}";
        $cacheTag = 'fresnsSeo';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $seoData = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($seoData)) {
            $seoData = Seo::where('usage_type', $usageType)->where('usage_id', $usageId)->first();

            CacheHelper::put($seoData, $cacheKey, $cacheTag);
        }

        return $seoData;
    }

    // get conversation model
    public static function fresnsModelConversation(int $authUserId, int $conversationUserId): Conversation
    {
        $cacheKey = "fresns_model_conversation_{$authUserId}_{$conversationUserId}";
        $cacheTag = 'fresnsUsers';

        $conversationModel = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($conversationModel)) {
            $aConversation = Conversation::where('a_user_id', $authUserId)->where('b_user_id', $conversationUserId)->first();
            $bConversation = Conversation::where('a_user_id', $conversationUserId)->where('b_user_id', $authUserId)->first();

            $conversationModel = $aConversation ?? $bConversation;

            if (empty($conversationModel)) {
                $item = [
                    'a_user_id' => $authUserId,
                    'b_user_id' => $conversationUserId,
                ];

                $conversationModel = Conversation::create($item);
            }

            CacheHelper::put($conversationModel, $cacheKey, $cacheTag);
        }

        return $conversationModel;
    }

    // get primary id
    public static function fresnsPrimaryId(string $type, ?string $fsid = null): ?int
    {
        if (empty($fsid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid($type, $fsid)?->id;
    }

    // get subgroups id array
    public static function fresnsSubgroupsIdArr(int|string $idOrGid): ?array
    {
        $cacheKey = "fresns_group_subgroups_ids_{$idOrGid}";
        $cacheTag = 'fresnsGroups';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $subgroups = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($subgroups)) {
            if (StrHelper::isPureInt($idOrGid)) {
                $groupModel = Group::where('id', $idOrGid)->first();
            } else {
                $groupModel = Group::where('gid', $idOrGid)->first();
            }

            $flattenGroups = $groupModel->flattenGroups();

            $subgroups = $flattenGroups->pluck('id')->toArray();

            CacheHelper::put($subgroups, $cacheKey, $cacheTag);
        }

        return $subgroups;
    }

    // get account id
    public static function fresnsAccountIdByUserId(?string $userId = null): ?int
    {
        if (empty($userId)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $userId)?->account_id;
    }

    // get group id
    public static function fresnsGroupIdByContentFsid(string $type, ?string $fsid = null): ?int
    {
        if (empty($fsid)) {
            return null;
        }

        $model = PrimaryHelper::fresnsModelByFsid($type, $fsid);

        if ($type == 'comment') {
            $model = PrimaryHelper::fresnsModelById('post', $model?->post_id);
        }

        $groupId = $model?->group_id;

        return $groupId;
    }
}

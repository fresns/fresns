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
use App\Models\Config;
use App\Models\Conversation;
use App\Models\Extend;
use App\Models\File;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Operation;
use App\Models\Post;
use App\Models\SessionKey;
use App\Models\User;
use App\Models\UserFollow;

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

        $cacheTags = match ($modelName) {
            'key' => ['fresnsModels', 'fresnsSystems'],
            'account' => ['fresnsModels', 'fresnsAccounts'],
            'user' => ['fresnsModels', 'fresnsUsers'],
            'group' => ['fresnsModels', 'fresnsGroups'],
            'hashtag' => ['fresnsModels', 'fresnsHashtags'],
            'post' => ['fresnsModels', 'fresnsPosts'],
            'comment' => ['fresnsModels', 'fresnsComments'],
            'file' => ['fresnsModels', 'fresnsFiles'],
            'extend' => ['fresnsModels', 'fresnsExtends'],
            'archive' => ['fresnsModels', 'fresnsArchives'],
            default => 'fresnsModels',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $fresnsModel = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($fresnsModel)) {
            switch ($modelName) {
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

                case 'post':
                    $fresnsModel = Post::withTrashed()->with(['postAppend', 'author', 'group', 'hashtags'])->where('pid', $fsid)->first();
                    break;

                case 'comment':
                    $fresnsModel = Comment::withTrashed()->with(['commentAppend', 'post', 'postAppend', 'author', 'hashtags'])->where('cid', $fsid)->first();
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

                default:
                    throw new \RuntimeException("unknown modelName {$modelName}");
                    break;
            }

            CacheHelper::put($fresnsModel, $cacheKey, $cacheTags);
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
        $cacheTags = match ($modelName) {
            'account' => ['fresnsModels', 'fresnsAccounts'],
            'user' => ['fresnsModels', 'fresnsUsers'],
            'group' => ['fresnsModels', 'fresnsGroups'],
            'hashtag' => ['fresnsModels', 'fresnsHashtags'],
            'post' => ['fresnsModels', 'fresnsPosts'],
            'comment' => ['fresnsModels', 'fresnsComments'],
            'file' => ['fresnsModels', 'fresnsFiles'],
            'extend' => ['fresnsModels', 'fresnsExtends'],
            'archive' => ['fresnsModels', 'fresnsArchives'],
            'operation' => ['fresnsModels', 'fresnsOperations'],
            'conversation' => ['fresnsModels', 'fresnsConversations'],
            default => 'fresnsModels',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $fresnsModel = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($fresnsModel)) {
            switch ($modelName) {
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

                case 'post':
                    $fresnsModel = Post::withTrashed()->with(['postAppend', 'author', 'group', 'hashtags'])->where('id', $id)->first();
                    break;

                case 'comment':
                    $fresnsModel = Comment::withTrashed()->with(['commentAppend', 'post', 'postAppend', 'author', 'hashtags'])->where('id', $id)->first();
                    break;

                case 'file':
                    $fresnsModel = File::withTrashed()->where('id', $id)->first();
                    break;

                case 'extend':
                    $fresnsModel = Extend::withTrashed()->where('id', $id)->first();
                    break;

                case 'operation':
                    $fresnsModel = Operation::withTrashed()->where('id', $id)->first();
                    break;

                case 'archive':
                    $fresnsModel = Archive::withTrashed()->where('id', $id)->first();
                    break;

                case 'conversation':
                    $fresnsModel = Conversation::withTrashed()->with(['aUser', 'bUser', 'latestMessage'])->where('id', $id)->first();
                    break;

                default:
                    throw new \RuntimeException("unknown modelName {$modelName}");
                    break;
            }

            CacheHelper::put($fresnsModel, $cacheKey, $cacheTags);
        }

        return $fresnsModel;
    }

    // get groups model
    public static function fresnsModelGroups(int|string $idOrGid): mixed
    {
        $cacheKey = "fresns_model_groups_{$idOrGid}";
        $cacheTags = ['fresnsModels', 'fresnsGroups'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $flattenGroups = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($flattenGroups)) {
            if (StrHelper::isPureInt($idOrGid)) {
                $groupModel = Group::where('id', $idOrGid)->first();
            } else {
                $groupModel = Group::where('gid', $idOrGid)->first();
            }

            $flattenGroups = $groupModel->flattenGroups();

            CacheHelper::put($flattenGroups, $cacheKey, $cacheTags);
        }

        return $flattenGroups;
    }

    // get conversation model
    public static function fresnsModelConversation(int $authUserId, int $conversationUserId): Conversation
    {
        $cacheKey = "fresns_model_conversation_{$authUserId}_{$conversationUserId}";
        $cacheTags = ['fresnsModels', 'fresnsUsers'];

        $conversationModel = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($conversationModel)) {
            $aConversation = Conversation::where('a_user_id', $conversationUserId)->where('b_user_id', $authUserId)->first();
            $bConversation = Conversation::where('b_user_id', $conversationUserId)->where('a_user_id', $authUserId)->first();

            if (empty($aConversation) && empty($bConversation)) {
                $conversationColumn['a_user_id'] = $authUserId;
                $conversationColumn['b_user_id'] = $conversationUserId;

                $conversationModel = Conversation::create($conversationColumn);
            } elseif (empty($aConversation)) {
                $conversationModel = $bConversation;
            } else {
                $conversationModel = $aConversation;
            }

            CacheHelper::put($conversationModel, $cacheKey, $cacheTags);
        }

        return $conversationModel;
    }

    // get follow model by type
    public static function fresnsFollowModelByType(string $type, int $id, ?int $authUserId = null): ?UserFollow
    {
        if (empty($authUserId)) {
            return null;
        }

        $cacheKey = "fresns_model_follow_{$type}_{$id}_by_{$authUserId}";
        $cacheTags = match ($type) {
            'user' => ['fresnsModels', 'fresnsUsers'],
            'group' => ['fresnsModels', 'fresnsGroups'],
            'hashtag' => ['fresnsModels', 'fresnsHashtags'],
            'post' => ['fresnsModels', 'fresnsPosts'],
            'comment' => ['fresnsModels', 'fresnsComments'],
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $fresnsModel = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($fresnsModel)) {
            $followType = match ($type) {
                'user' => UserFollow::TYPE_USER,
                'group' => UserFollow::TYPE_GROUP,
                'hashtag' => UserFollow::TYPE_HASHTAG,
                'post' => UserFollow::TYPE_POST,
                'comment' => UserFollow::TYPE_COMMENT,
                default => null,
            };

            if (empty($followType)) {
                throw new \RuntimeException("unknown type {$type}");
            }

            $fresnsModel = UserFollow::where('user_id', $authUserId)->where('follow_type', $followType)->where('follow_id', $id)->first();

            CacheHelper::put($fresnsModel, $cacheKey, $cacheTags);
        }

        return $fresnsModel;
    }

    // get table id
    public static function fresnsPrimaryId(string $tableName, ?string $tableKey = null): ?int
    {
        if (empty($tableKey)) {
            return null;
        }

        $tableId = match ($tableName) {
            'config' => PrimaryHelper::fresnsConfigIdByItemKey($tableKey),
            'account' => PrimaryHelper::fresnsAccountIdByAid($tableKey),
            'user' => PrimaryHelper::fresnsUserIdByUidOrUsername($tableKey),
            'group' => PrimaryHelper::fresnsGroupIdByGid($tableKey),
            'hashtag' => PrimaryHelper::fresnsHashtagIdByHid($tableKey),
            'post' => PrimaryHelper::fresnsPostIdByPid($tableKey),
            'comment' => PrimaryHelper::fresnsCommentIdByCid($tableKey),
            'file' => PrimaryHelper::fresnsFileIdByFid($tableKey),
            'extend' => PrimaryHelper::fresnsExtendIdByEid($tableKey),

            'configs' => PrimaryHelper::fresnsConfigIdByItemKey($tableKey),
            'accounts' => PrimaryHelper::fresnsAccountIdByAid($tableKey),
            'users' => PrimaryHelper::fresnsUserIdByUidOrUsername($tableKey),
            'groups' => PrimaryHelper::fresnsGroupIdByGid($tableKey),
            'hashtags' => PrimaryHelper::fresnsHashtagIdByHid($tableKey),
            'posts' => PrimaryHelper::fresnsPostIdByPid($tableKey),
            'comments' => PrimaryHelper::fresnsCommentIdByCid($tableKey),
            'files' => PrimaryHelper::fresnsFileIdByFid($tableKey),
            'extends' => PrimaryHelper::fresnsExtendIdByEid($tableKey),

            default => null,
        };

        return $tableId;
    }

    public static function fresnsConfigIdByItemKey(?string $itemKey = null): ?int
    {
        if (empty($itemKey)) {
            return null;
        }

        $id = Config::withTrashed()->where('item_key', $itemKey)->value('id');

        return $id ?? null;
    }

    public static function fresnsAccountIdByAid(?string $aid = null): ?int
    {
        if (empty($aid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('account', $aid)?->id;
    }

    public static function fresnsAccountIdByUserId(?string $userId = null): ?int
    {
        if (empty($userId)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $userId)?->account_id;
    }

    public static function fresnsAccountIdByUidOrUsername(?string $uidOrUsername = null): ?int
    {
        if (empty($uidOrUsername)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername)?->account_id;
    }

    public static function fresnsUserIdByUidOrUsername(?string $uidOrUsername = null): ?int
    {
        if (empty($uidOrUsername)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $uidOrUsername)?->id;
    }

    public static function fresnsGroupIdByGid(?string $gid = null): ?int
    {
        if (empty($gid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('group', $gid)?->id;
    }

    public static function fresnsHashtagIdByHid(?string $hid = null): ?int
    {
        if (empty($hid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('hashtag', $hid)?->id;
    }

    public static function fresnsPostIdByPid(?string $pid = null): ?int
    {
        if (empty($pid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('post', $pid)?->id;
    }

    public static function fresnsCommentIdByCid(?string $cid = null): ?int
    {
        if (empty($cid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('comment', $cid)?->id;
    }

    public static function fresnsFileIdByFid(?string $fid = null): ?int
    {
        if (empty($fid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('file', $fid)?->id;
    }

    public static function fresnsExtendIdByEid(?string $eid = null): ?int
    {
        if (empty($eid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('extend', $eid)?->id;
    }

    public static function fresnsArchiveIdByCode(?string $code = null): ?int
    {
        if (empty($code)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('archive', $code)?->id;
    }
}

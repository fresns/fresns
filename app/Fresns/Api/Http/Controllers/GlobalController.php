<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\GlobalArchivesDTO;
use App\Fresns\Api\Http\DTO\GlobalBlockWordsDTO;
use App\Fresns\Api\Http\DTO\GlobalCodeMessagesDTO;
use App\Fresns\Api\Http\DTO\GlobalConfigsDTO;
use App\Fresns\Api\Http\DTO\GlobalRolesDTO;
use App\Fresns\Api\Http\DTO\GlobalUploadTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\Archive;
use App\Models\BlockWord;
use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\File;
use App\Models\PluginUsage;
use App\Models\Role;
use App\Models\Sticker;
use App\Utilities\ExtendUtility;
use App\Utilities\GeneralUtility;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    // status
    public function status()
    {
        $statusJson = [
            'name' => 'Fresns',
            'activate' => true,
            'deactivateDescribe' => [
                'default' => '',
            ],
        ];

        $statusJsonFile = public_path('status.json');

        if (file_exists($statusJsonFile)) {
            $statusJson = json_decode(file_get_contents($statusJsonFile), true);
        }

        return $statusJson;
    }

    // configs
    public function configs(Request $request)
    {
        $dtoRequest = new GlobalConfigsDTO($request->all());
        $langTag = $this->langTag();

        $cacheKey = "fresns_api_configs_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        $configs = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($configs)) {
            $configModels = Config::where('is_api', 1)->get();

            $configs = null;
            foreach ($configModels as $model) {
                $itemValue = null;

                switch ($model->item_type) {
                    case 'file':
                        $itemValue = ConfigHelper::fresnsConfigFileUrlByItemKey($model->item_key);
                        break;

                    case 'plugin':
                        $itemValue = PluginHelper::fresnsPluginUrlByFskey($model->item_value) ?? $model->item_value;
                        break;

                    case 'plugins':
                        $itemValue = [];
                        if ($model->item_value) {
                            foreach ($model->item_value as $plugin) {
                                $pluginItem['code'] = $plugin['code'];
                                $pluginItem['url'] = PluginHelper::fresnsPluginUrlByFskey($plugin['fskey']);

                                $itemArr[] = $pluginItem;
                            }

                            $itemValue = $itemArr;
                        }
                        break;

                    default:
                        $itemValue = $model->is_multilingual ? StrHelper::languageContent($model->item_value, $langTag) : $model->item_value;
                }

                $configs[$model->item_key] = $itemValue;
            }

            $configs['cache_minutes'] = ConfigHelper::fresnsConfigFileUrlExpire();

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($configs, $cacheTag, $cacheTag, 10, $cacheTime);
        }

        if (empty($dtoRequest->keys)) {
            return $this->success($configs);
        }

        $itemKeys = array_filter(explode(',', $dtoRequest->keys));

        $items = [];
        foreach ($itemKeys as $itemKey) {
            $items[$itemKey] = $configs[$itemKey];
        }

        return $this->success($items);
    }

    // code messages
    public function codeMessages(Request $request)
    {
        $dtoRequest = new GlobalCodeMessagesDTO($request->all());

        $langTag = $this->langTag();
        $fskey = $dtoRequest->fskey ?? 'Fresns';
        $isAll = $dtoRequest->isAll ?? false;

        $cacheKey = "fresns_code_messages_{$fskey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $codeMessages = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($codeMessages)) {
            $codeMessages = CodeMessage::where('plugin_fskey', $fskey)->where('lang_tag', $langTag)->get();

            if (empty($codeMessages)) {
                $codeMessages = CodeMessage::where('plugin_fskey', $fskey)->where('lang_tag', 'en')->get();
            }

            CacheHelper::put($codeMessages, $cacheKey, $cacheTag);
        }

        if ($isAll) {
            $messages = $codeMessages;
        } else {
            $codeArr = array_filter(explode(',', $dtoRequest->codes));

            $messages = $codeMessages->whereIn('code', $codeArr);
        }

        $item = null;
        foreach ($messages as $message) {
            $item[$message->code] = $message->message;
        }

        return $this->success($item);
    }

    // channels
    public function channels(Request $request)
    {
        $channels = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_CHANNEL, null, null, $this->langTag());
        $authUserId = $this->user()?->id;

        $channelList = [];
        foreach ($channels as $channel) {
            $badge = ExtendUtility::getPluginBadge($channel['fskey'], $authUserId);

            $channel['badgeType'] = $badge['badgeType'];
            $channel['badgeValue'] = $badge['badgeValue'];

            $channelList[] = $channel;
        }

        return $this->success($channelList);
    }

    // archives
    public function archives($type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new GlobalArchivesDTO($requestData);

        $langTag = $this->langTag();
        $fskey = $dtoRequest->fskey ?? null;

        $usageType = match ($dtoRequest->type) {
            'user' => Archive::TYPE_USER,
            'group' => Archive::TYPE_GROUP,
            'hashtag' => Archive::TYPE_HASHTAG,
            'post' => Archive::TYPE_POST,
            'comment' => Archive::TYPE_COMMENT,
        };

        $cacheKey = "fresns_api_archives_{$dtoRequest->type}_{$fskey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $archives = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($archives)) {
            $archiveData = Archive::type($usageType)
                ->when($fskey, function ($query, $value) {
                    $query->where('plugin_fskey', $value);
                })
                ->where('usage_group_id', 0)
                ->isEnabled()
                ->orderBy('sort_order')
                ->get();

            $items = [];
            foreach ($archiveData as $archive) {
                $items[] = $archive->getArchiveInfo($langTag);
            }

            $archives = $items;

            CacheHelper::put($archives, $cacheKey, $cacheTag);
        }

        return $this->success($archives);
    }

    // get upload token
    public function uploadToken(Request $request)
    {
        $dtoRequest = new GlobalUploadTokenDTO($request->all());

        $fileType = match ($dtoRequest->type) {
            'image' => 1,
            'video' => 2,
            'audio' => 3,
            'document' => 4,
        };

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($fileType);

        if (! $storageConfig['storageConfigStatus']) {
            throw new ApiException(32103);
        }

        $wordBody = [
            'type' => $fileType,
            'name' => $dtoRequest->name,
            'expireTime' => $dtoRequest->expireTime,
        ];

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getUploadToken($wordBody);

        return $fresnsResp->getOrigin();
    }

    // roles
    public function roles(Request $request)
    {
        $dtoRequest = new GlobalRolesDTO($request->all());
        $langTag = $this->langTag();

        $roleQuery = Role::orderBy('sort_order');

        if (isset($dtoRequest->status)) {
            $roleQuery->where('is_enabled', $dtoRequest->status);
        }

        if ($dtoRequest->ids) {
            $ids = array_filter(explode(',', $dtoRequest->ids));
            $roleQuery->whereIn('id', $ids);
        }

        $roleQuery->when($dtoRequest->type, function ($query, $value) {
            $query->where('type', $value);
        });

        $roles = $roleQuery->paginate($dtoRequest->pageSize ?? 15);

        $roleList = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissions[$perm['permKey']] = $perm['permValue'];
            }

            $item['type'] = $role->type;
            $item['rid'] = $role->id;
            $item['nicknameColor'] = $role->nickname_color;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role->id, $langTag);
            $item['nameDisplay'] = (bool) $role->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($role->icon_file_id, $role->icon_file_url);
            $item['iconDisplay'] = (bool) $role->is_display_icon;
            $item['permissions'] = $permissions;
            $item['status'] = (bool) $role->is_enabled;
            $roleList[] = $item;
        }

        return $this->fresnsPaginate($roleList, $roles->total(), $roles->perPage());
    }

    // contentTypes
    public function contentTypes($type)
    {
        $scene = match ($type) {
            'post' => 1,
            'comment' => 2,
            'posts' => 1,
            'comments' => 2,
            default => null,
        };

        if (empty($scene)) {
            throw new ApiException(30002);
        }

        $langTag = $this->langTag();

        $data = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_CONTENT, null, null, $langTag);

        return $this->success($data);
    }

    // stickers
    public function stickers()
    {
        $langTag = $this->langTag();

        $cacheKey = "fresns_api_sticker_tree_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $stickerTree = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($stickerTree)) {
            $stickers = Sticker::isEnabled()->orderBy('sort_order')->get();

            $stickerData = [];
            foreach ($stickers as $index => $sticker) {
                $stickerData[$index]['parentCode'] = $stickers->where('id', $sticker->parent_id)->first()?->code;
                $stickerData[$index]['name'] = LanguageHelper::fresnsLanguageByTableId('stickers', 'name', $sticker->id, $langTag);
                $stickerData[$index]['code'] = $sticker->code;
                $stickerData[$index]['codeFormat'] = '['.$sticker->code.']';
                $stickerData[$index]['image'] = FileHelper::fresnsFileUrlByTableColumn($sticker->image_file_id, $sticker->image_file_url);
            }

            $stickerTree = GeneralUtility::collectionToTree($stickerData, 'code', 'parentCode', 'stickers');

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($stickerTree, $cacheKey, $cacheTag, null, $cacheTime);
        }

        return $this->success($stickerTree);
    }

    // blockWords
    public function blockWords(Request $request)
    {
        $dtoRequest = new GlobalBlockWordsDTO($request->all());

        $wordQuery = BlockWord::all();

        if ($dtoRequest->type == 'content') {
            $wordQuery = BlockWord::where('content_mode', '!=', 1);
        } elseif ($dtoRequest->type == 'user') {
            $wordQuery = BlockWord::where('user_mode', '!=', 1);
        } elseif ($dtoRequest->type == 'conversation') {
            $wordQuery = BlockWord::where('conversation_mode', '!=', 1);
        }

        $words = $wordQuery->paginate($dtoRequest->pageSize ?? 50);

        $wordList = [];
        foreach ($words as $word) {
            $item['word'] = $word->word;
            $item['contentMode'] = $word->content_mode;
            $item['userMode'] = $word->user_mode;
            $item['conversationMode'] = $word->conversation_mode;
            $item['replaceWord'] = $word->replace_word;

            $wordList[] = $item;
        }

        return $this->fresnsPaginate($wordList, $words->total(), $words->perPage());
    }
}

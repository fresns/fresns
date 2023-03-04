<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
use App\Models\Archive;
use App\Models\BlockWord;
use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\File;
use App\Models\PluginUsage;
use App\Models\Role;
use App\Models\Sticker;
use App\Utilities\CollectionUtility;
use App\Utilities\ExtendUtility;
use Illuminate\Http\Request;

class GlobalController extends Controller
{
    // configs
    public function configs(Request $request)
    {
        $dtoRequest = new GlobalConfigsDTO($request->all());
        $langTag = $this->langTag();

        $itemKey = array_filter(explode(',', $dtoRequest->keys));
        $itemTag = array_filter(explode(',', $dtoRequest->tags));

        $configQuery = Config::where('is_api', 1);

        if ($dtoRequest->isAll) {
            $configs = $configQuery->get();

            $total = $configs->count();
            $perPage = $total;
        } else {
            if (! empty($itemKey) && ! empty($itemTag)) {
                $configQuery->whereIn('item_key', $itemKey)->orWhereIn('item_tag', $itemTag);
            } elseif (! empty($itemKey) && empty($itemTag)) {
                $configQuery->whereIn('item_key', $itemKey);
            } elseif (empty($itemKey) && ! empty($itemTag)) {
                $configQuery->whereIn('item_tag', $itemTag);
            }

            $configs = $configQuery->paginate($dtoRequest->pageSize ?? 50);

            $total = $configs->total();
            $perPage = $configs->perPage();
        }

        $item = null;
        foreach ($configs as $config) {
            if ($config->is_multilingual) {
                $item[$config->item_key] = LanguageHelper::fresnsLanguageByTableKey($config->item_key, $config->item_type, $langTag);
            } elseif ($config->item_type == 'file') {
                $item[$config->item_key] = ConfigHelper::fresnsConfigFileUrlByItemKey($config->item_key);
            } elseif ($config->item_type == 'plugin') {
                $item[$config->item_key] = PluginHelper::fresnsPluginUrlByUnikey($config->item_value) ?? $config->item_value;
            } elseif ($config->item_type == 'plugins') {
                if ($config->item_value) {
                    foreach ($config->item_value as $plugin) {
                        $pluginItem['code'] = $plugin['code'];
                        $pluginItem['url'] = PluginHelper::fresnsPluginUrlByUnikey($plugin['unikey']);
                        $itemArr[] = $pluginItem;
                    }
                    $item[$config->item_key] = $itemArr;
                }
            } else {
                $item[$config->item_key] = $config->item_value;
            }
        }

        if (in_array('cache_datetime', $itemKey) || in_array('cache_minutes', $itemKey)) {
            $item['cache_minutes'] = ConfigHelper::fresnsConfigFileUrlExpire();
        }

        return $this->fresnsPaginate($item, $total, $perPage);
    }

    // code messages
    public function codeMessages(Request $request)
    {
        $dtoRequest = new GlobalCodeMessagesDTO($request->all());

        $langTag = $this->langTag();
        $unikey = $dtoRequest->unikey ?? 'Fresns';
        $isAll = $dtoRequest->isAll ?? false;

        $cacheKey = "fresns_code_messages_{$unikey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $codeMessages = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($codeMessages)) {
            $codeMessages = CodeMessage::where('plugin_unikey', $unikey)->where('lang_tag', $langTag)->get();

            if (empty($codeMessages)) {
                $codeMessages = CodeMessage::where('plugin_unikey', $unikey)->where('lang_tag', 'en')->get();
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

    // archives
    public function archives($type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new GlobalArchivesDTO($requestData);

        $langTag = $this->langTag();
        $unikey = $dtoRequest->unikey ?? null;

        $usageType = match ($dtoRequest->type) {
            'user' => Archive::TYPE_USER,
            'group' => Archive::TYPE_GROUP,
            'hashtag' => Archive::TYPE_HASHTAG,
            'post' => Archive::TYPE_POST,
            'comment' => Archive::TYPE_COMMENT,
        };

        $cacheKey = "fresns_api_archives_{$dtoRequest->type}_{$unikey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $archives = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($archives)) {
            $archiveData = Archive::type($usageType)
                ->when($unikey, function ($query, $value) {
                    $query->where('plugin_unikey', $value);
                })
                ->where('usage_group_id', 0)
                ->isEnable()
                ->orderBy('rating')
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

        $roleQuery = Role::orderBy('rating');

        if (isset($dtoRequest->status)) {
            $roleQuery->where('is_enable', $dtoRequest->status);
        }

        if (! empty($dtoRequest->ids)) {
            $ids = array_filter(explode(',', $dtoRequest->ids));
            $roleQuery->whereIn('id', $ids);
        }

        if (! empty($dtoRequest->type)) {
            $roleQuery->where('type', $dtoRequest->type);
        }

        $roles = $roleQuery->paginate($dtoRequest->pageSize ?? 15);

        $roleList = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissions[$perm['permKey']] = $perm['permValue'];
            }

            $item['rid'] = $role->id;
            $item['nicknameColor'] = $role->nickname_color;
            $item['name'] = LanguageHelper::fresnsLanguageByTableId('roles', 'name', $role->id, $langTag);
            $item['nameDisplay'] = (bool) $role->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($role->icon_file_id, $role->icon_file_url);
            $item['iconDisplay'] = (bool) $role->is_display_icon;
            $item['permissions'] = $permissions;
            $item['status'] = (bool) $role->is_enable;
            $roleList[] = $item;
        }

        return $this->fresnsPaginate($roleList, $roles->total(), $roles->perPage());
    }

    // maps
    public function maps()
    {
        $langTag = $this->langTag();

        $data = ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_MAP, null, null, $langTag);

        return $this->success($data);
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
            $stickers = Sticker::isEnable()->orderBy('rating')->get();

            $stickerData = [];
            foreach ($stickers as $index => $sticker) {
                $stickerData[$index]['parentCode'] = $stickers->where('id', $sticker->parent_id)->first()?->code;
                $stickerData[$index]['name'] = LanguageHelper::fresnsLanguageByTableId('stickers', 'name', $sticker->id, $langTag);
                $stickerData[$index]['code'] = $sticker->code;
                $stickerData[$index]['codeFormat'] = '['.$sticker->code.']';
                $stickerData[$index]['image'] = FileHelper::fresnsFileUrlByTableColumn($sticker->image_file_id, $sticker->image_file_url);
            }

            $stickerTree = CollectionUtility::toTree($stickerData, 'code', 'parentCode', 'stickers');

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

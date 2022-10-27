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
use App\Models\Config;
use App\Models\File;
use App\Models\PluginUsage;
use App\Models\Role;
use App\Models\Sticker;
use App\Utilities\CollectionUtility;
use App\Utilities\ExtendUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        if (! empty($itemKey) && ! empty($itemTag)) {
            $configQuery->whereIn('item_key', $itemKey)->orWhereIn('item_tag', $itemTag);
        } elseif (! empty($itemKey) && empty($itemTag)) {
            $configQuery->whereIn('item_key', $itemKey);
        } elseif (empty($itemKey) && ! empty($itemTag)) {
            $configQuery->whereIn('item_tag', $itemTag);
        }

        if ($dtoRequest->isAll) {
            $configs = $configQuery->get();
            $total = $configs->count();
            $perPage = $total;
        } else {
            $configs = $configQuery->paginate($request->get('pageSize', 50));

            $total = $configs->total();
            $perPage = $configs->perPage();
        }

        $item = null;
        foreach ($configs as $config) {
            if ($config->is_multilingual == 1) {
                $item[$config->item_key] = LanguageHelper::fresnsLanguageByTableKey($config->item_key, $config->item_type, $langTag);
            } elseif ($config->item_type == 'file' && StrHelper::isPureInt($config->item_value)) {
                $item[$config->item_key] = ConfigHelper::fresnsConfigFileUrlByItemKey($config->item_value);
            } elseif ($config->item_type == 'plugin') {
                $item[$config->item_key] = PluginHelper::fresnsPluginUrlByUnikey($config->item_value);
            } elseif ($config->item_type == 'plugins') {
                if ($config->item_value) {
                    foreach ($config->item_value as $plugin) {
                        $item['code'] = $plugin['code'];
                        $item['url'] = PluginHelper::fresnsPluginUrlByUnikey($plugin['unikey']);
                        $itemArr[] = $item;
                    }
                    $item[$config->item_key] = $itemArr;
                }
            } else {
                $item[$config->item_key] = $config->item_value;
            }
        }

        return $this->fresnsPaginate($item, $total, $perPage);
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
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $archives = Cache::remember($cacheKey, $cacheTime, function () use ($usageType, $unikey) {
            $archiveData = Archive::type($usageType)
                ->when($unikey, function ($query, $value) {
                    $query->where('plugin_unikey', $value);
                })
                ->isEnable()
                ->orderBy('rating')
                ->get();

            $fileExtName = ConfigHelper::fresnsConfigByItemKeys([
                'image_extension_names',
                'video_extension_names',
                'audio_extension_names',
                'document_extension_names',
            ]);

            $items = [];
            foreach ($archiveData as $archive) {
                $fileExt = match ($archive->file_type) {
                    1 => $fileExtName['image_extension_names'],
                    2 => $fileExtName['video_extension_names'],
                    3 => $fileExtName['audio_extension_names'],
                    4 => $fileExtName['document_extension_names'],
                    default => null,
                };

                $item['plugin'] = $archive->plugin_unikey;
                $item['name'] = $archive->name;
                $item['code'] = $archive->code;
                $item['formElement'] = $archive->form_element;
                $item['elementType'] = $archive->element_type;
                $item['elementOptions'] = $archive->element_options;
                $item['fileType'] = $archive->file_type;
                $item['fileExt'] = Str::lower($fileExt);
                $item['fileAccept'] = FileHelper::fresnsFileAcceptByType($archive->file_type);
                $item['isMultiple'] = (bool) $archive->is_multiple;
                $item['isRequired'] = (bool) $archive->is_required;
                $item['inputPattern'] = $archive->input_pattern;
                $item['inputMax'] = $archive->input_max;
                $item['inputMin'] = $archive->input_min;
                $item['inputMaxlength'] = $archive->input_maxlength;
                $item['inputMinlength'] = $archive->input_minlength;
                $item['inputSize'] = $archive->input_size;
                $item['inputStep'] = $archive->input_step;

                $items[] = $item;
            }

            return $items;
        });

        if (is_null($archives)) {
            Cache::forget($cacheKey);
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

        $status = $dtoRequest->status ?? 1;

        $roleQuery = Role::isEnable($status)->orderBy('rating');

        if (! empty($dtoRequest->ids)) {
            $ids = array_filter(explode(',', $dtoRequest->ids));
            $roleQuery->whereIn('id', $ids);
        }

        if (! empty($dtoRequest->type)) {
            $roleQuery->where('type', $dtoRequest->type);
        }

        $roles = $roleQuery->paginate($request->get('pageSize', 15));

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

        $data = ExtendUtility::getPluginUsages(PluginUsage::TYPE_MAP, null, null, null, $langTag);

        return $this->success($data);
    }

    // contentType
    public function contentType()
    {
        $langTag = $this->langTag();

        $data = ExtendUtility::getPluginUsages(PluginUsage::TYPE_CONTENT, null, null, null, $langTag);

        return $this->success($data);
    }

    // stickers
    public function stickers()
    {
        $langTag = $this->langTag();

        $cacheKey = "fresns_api_stickers_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $stickerTree = Cache::remember($cacheKey, $cacheTime, function () use ($langTag) {
            $stickers = Sticker::isEnable()->orderBy('rating')->get();

            $stickerData = [];
            foreach ($stickers as $index => $sticker) {
                $stickerData[$index]['parentCode'] = $stickers->where('id', $sticker->parent_id)->first()?->code;
                $stickerData[$index]['name'] = LanguageHelper::fresnsLanguageByTableId('stickers', 'name', $sticker->id, $langTag);
                $stickerData[$index]['code'] = $sticker->code;
                $stickerData[$index]['codeFormat'] = '['.$sticker->code.']';
                $stickerData[$index]['image'] = FileHelper::fresnsFileUrlByTableColumn($sticker->image_file_id, $sticker->image_file_url);
            }

            return CollectionUtility::toTree($stickerData, 'code', 'parentCode', 'stickers');
        });

        if (is_null($stickerTree)) {
            Cache::forget($cacheKey);
        }

        return $this->success($stickerTree);
    }

    // blockWords
    public function blockWords(Request $request)
    {
        $dtoRequest = new GlobalBlockWordsDTO($request->all());

        $wordQuery = BlockWord::all();

        if ($dtoRequest->type = 'content') {
            $wordQuery = BlockWord::where('content_mode', '!=', 1);
        } elseif ($dtoRequest->type = 'user') {
            $wordQuery = BlockWord::where('user_mode', '!=', 1);
        } elseif ($dtoRequest->type = 'dialog') {
            $wordQuery = BlockWord::where('dialog_mode', '!=', 1);
        }

        $words = $wordQuery->paginate($request->get('pageSize', 50));

        $wordList = [];
        foreach ($words as $word) {
            $item['word'] = $word->word;
            $item['contentMode'] = $word->content_mode;
            $item['userMode'] = $word->user_mode;
            $item['dialogMode'] = $word->dialog_mode;
            $item['replaceWord'] = $word->replace_word;
            $wordList[] = $item;
        }

        return $this->fresnsPaginate($wordList, $words->total(), $words->perPage());
    }
}

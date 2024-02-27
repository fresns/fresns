<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\GlobalArchivesDTO;
use App\Fresns\Api\Http\DTO\GlobalConfigsDTO;
use App\Fresns\Api\Http\DTO\GlobalRolesDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\AppUsage;
use App\Models\Archive;
use App\Models\Config;
use App\Models\File;
use App\Models\LanguagePack;
use App\Models\Role;
use App\Models\Sticker;
use App\Utilities\ExtendUtility;
use App\Utilities\GeneralUtility;
use App\Utilities\PermissionUtility;
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

            // account center
            $accountConfigs = ConfigHelper::fresnsConfigByItemKeys([
                'account_center_service',
                'account_register_service',
                'account_login_service',
            ]);

            if (empty($accountConfigs['account_center_service'])) {
                $configs['account_center_service'] = config('app.url').'/account-center?accessToken={accessToken}&callbackKey={postMessageKey}';
            }

            if (empty($accountConfigs['account_register_service'])) {
                $configs['account_register_service'] = config('app.url').'/account-center/sign-up?accessToken={accessToken}&callbackKey={postMessageKey}';
            }

            if (empty($accountConfigs['account_login_service'])) {
                $configs['account_login_service'] = config('app.url').'/account-center/login?accessToken={accessToken}&callbackKey={postMessageKey}';
            }

            // cache minutes
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

    // language pack
    public function languagePack()
    {
        $langTag = $this->langTag();

        $cacheKey = "fresns_language_pack_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->success([]);
        }

        $languagePack = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($languagePack)) {
            $languages = LanguagePack::all();

            $languagePack = [];
            foreach ($languages as $language) {
                $languagePack[$language->lang_key] = StrHelper::languageContent($language->lang_values, $langTag);
            }

            CacheHelper::put($languagePack, $cacheKey, $cacheTag);
        }

        return $this->success($languagePack);
    }

    // channels
    public function channels()
    {
        $langTag = $this->langTag();
        $authUserId = $this->user()?->id;

        $channels = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_CHANNEL, null, null, $langTag);

        $roleArr = PermissionUtility::getUserRoles($authUserId);

        $roleChannels = [];
        foreach ($roleArr as $role) {
            $roleChannels[] = ExtendUtility::getAppExtendsByRole(AppUsage::TYPE_CHANNEL, $role['id'], null, null, $langTag);
        }

        $allChannels = array_merge($channels, $roleChannels);

        $channelList = [];
        foreach ($allChannels as $channel) {
            $badge = ExtendUtility::getAppBadge($channel['fskey'], $authUserId);

            $channel['badgeType'] = $badge['badgeType'];
            $channel['badgeValue'] = $badge['badgeValue'];

            unset($channel['editorToolbar']);
            unset($channel['editorNumber']);

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
            'geotag' => Archive::TYPE_GEOTAG,
            'post' => Archive::TYPE_POST,
            'comment' => Archive::TYPE_COMMENT,
        };

        $cacheKey = "fresns_api_archives_{$type}_{$fskey}_{$langTag}";
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
                    $query->where('app_fskey', $value);
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

        $data = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_CONTENT, null, null, $langTag);

        $types = array_map(function ($item) {
            unset($item['appUrl']);
            unset($item['editorToolbar']);
            unset($item['editorNumber']);

            return $item;
        }, $data);

        return $this->success($types);
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

        $roleQuery->when($dtoRequest->rids, function ($query, $value) {
            $rids = array_filter(explode(',', $value));

            $query->whereIn('rid', $rids);
        });

        $roles = $roleQuery->paginate($dtoRequest->pageSize ?? 15);

        $roleList = [];
        foreach ($roles as $role) {
            foreach ($role->permissions as $perm) {
                $permissions[$perm['permKey']] = $perm['permValue'];
            }

            $item['rid'] = $role->rid;
            $item['nicknameColor'] = $role->nickname_color;
            $item['name'] = StrHelper::languageContent($role->name, $langTag);
            $item['nameDisplay'] = (bool) $role->is_display_name;
            $item['icon'] = FileHelper::fresnsFileUrlByTableColumn($role->icon_file_id, $role->icon_file_url);
            $item['iconDisplay'] = (bool) $role->is_display_icon;
            $item['permissions'] = $permissions;
            $item['status'] = (bool) $role->is_enabled;

            $roleList[] = $item;
        }

        return $this->fresnsPaginate($roleList, $roles->total(), $roles->perPage());
    }

    // stickers
    public function stickers()
    {
        $langTag = $this->langTag();

        $cacheKey = "fresns_api_stickers_{$langTag}";
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
                $stickerData[$index]['name'] = StrHelper::languageContent($sticker->name, $langTag);
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
}

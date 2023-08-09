<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CodeMessage;
use App\Models\CommentLog;
use App\Models\Config;
use App\Models\File;
use App\Models\Language;
use App\Models\PostLog;
use App\Models\SessionLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ConfigUtility
{
    // add config items
    public static function addFresnsConfigItems(array $fresnsConfigItems): void
    {
        foreach ($fresnsConfigItems as $item) {
            $itemKey = $item['item_key'] ?? null;

            if (empty($itemKey)) {
                continue;
            }

            $configModel = Config::where('item_key', $itemKey)->first();

            if (empty($configModel)) {
                $itemArr = collect($item)->only('item_key', 'item_type', 'item_tag')->filter()->toArray();
                $itemArr['item_value'] = $item['item_value'];
                $itemArr['is_multilingual'] = $item['is_multilingual'];
                $itemArr['is_api'] = $item['is_api'];
                Config::create($itemArr);

                $isMultilingual = $item['is_multilingual'] ?? false;
                $hasLangValues = array_key_exists('language_values', $item);

                if ($isMultilingual && $hasLangValues) {
                    $fresnsLangItems = [
                        'table_name' => 'configs',
                        'table_column' => 'item_value',
                        'table_key' => $itemKey,
                        'language_values' => $item['language_values'],
                    ];
                    ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
                }
            }
        }
    }

    // remove config items
    public static function removeFresnsConfigItems(array $fresnsConfigKeys): void
    {
        foreach ($fresnsConfigKeys as $key) {
            $configModel = Config::where('item_key', $key)->where('is_custom', 1)->first();

            if ($configModel?->is_multilingual) {
                Language::where('table_name', 'configs')->where('table_column', 'item_value')->where('table_key', $key)->forceDelete();
            }

            $configModel?->forceDelete();
        }
    }

    // change config items
    public static function changeFresnsConfigItems(array $fresnsConfigItems): void
    {
        foreach ($fresnsConfigItems as $item) {
            $itemKey = $item['item_key'] ?? null;

            if (empty($itemKey)) {
                continue;
            }

            $itemArr = collect($item)->only('item_key', 'item_type', 'item_tag')->filter()->toArray();
            $itemArr['item_value'] = $item['item_value'];
            $itemArr['is_multilingual'] = $item['is_multilingual'];
            $itemArr['is_api'] = $item['is_api'];


            Config::updateOrCreate([
                'item_key' => $itemKey,
            ], $itemArr);

            $isMultilingual = $item['is_multilingual'] ?? false;
            $hasLangValues = array_key_exists('language_values', $item);

            if ($isMultilingual && $hasLangValues) {
                $fresnsLangItems = [
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_key' => $itemKey,
                    'language_values' => $item['language_values'] ?? [],
                ];
                ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
            }
        }
    }

    // change language items
    public static function changeFresnsLanguageItems($fresnsLangItems): void
    {
        $tableName = $fresnsLangItems['table_name'] ?? null;
        $tableColumn = $fresnsLangItems['table_column'] ?? null;
        $tableId = $fresnsLangItems['table_id'] ?? null;
        $tableKey = $fresnsLangItems['table_key'] ?? null;
        $languageValues = $fresnsLangItems['language_values'] ?? [];

        if (empty($tableName) || empty($tableColumn)) {
            return;
        }

        if (empty($tableId) && empty($tableKey)) {
            return;
        }

        if ($tableId) {
            $langArr = [
                'table_name' => $tableName,
                'table_column' => $tableColumn,
                'table_id' => $tableId,
                'lang_tag' => null,
            ];
        } else {
            $langArr = [
                'table_name' => $tableName,
                'table_column' => $tableColumn,
                'table_key' => $tableKey,
                'lang_tag' => null,
            ];
        }

        foreach ($languageValues as $key => $value) {
            $langArr['lang_tag'] = $key;

            Language::updateOrCreate($langArr, [
                'lang_content' => $value,
            ]);
        }
    }

    // get code message
    public static function getCodeMessage(int $code, ?string $fskey = null, ?string $langTag = null): string
    {
        $fskey = $fskey ?: 'Fresns';
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_code_messages_{$fskey}_{$langTag}";
        $cacheTag = 'fresnsConfigs';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return 'Unknown Error';
        }

        $codeMessages = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($codeMessages)) {
            $codeMessages = CodeMessage::where('plugin_fskey', $fskey)->where('lang_tag', $langTag)->get();

            if (empty($codeMessages)) {
                $codeMessages = CodeMessage::where('plugin_fskey', $fskey)->where('lang_tag', 'en')->get();
            }

            CacheHelper::put($codeMessages, $cacheKey, $cacheTag);
        }

        $message = $codeMessages->where('code', $code)?->value('message');

        return $message ?? 'Unknown Error';
    }

    // get login error count
    public static function getLoginErrorCount(int $accountId, ?int $userId = null): int
    {
        $typeArr = [
            SessionLog::TYPE_LOGIN_PANEL,
            SessionLog::TYPE_ACCOUNT_LOGIN,
            SessionLog::TYPE_USER_LOGIN,
        ];
        $resultState = [
            SessionLog::STATE_UNKNOWN,
            SessionLog::STATE_FAILURE,
        ];

        $sessionLog = SessionLog::whereIn('type', $typeArr)
            ->whereIn('object_result', $resultState)
            ->where('account_id', $accountId)
            ->where('created_at', '>=', now()->subHour());

        $sessionLog->when($userId, function ($query, $value) {
            $query->where('user_id', $value);
        });

        $errorCount = $sessionLog->count();

        return $errorCount;
    }

    // get editor config by type(post or comment)
    public static function getEditorConfigByType(int $userId, string $type, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $rolePerm = PermissionUtility::getUserMainRole($userId)['permissions'];
        $editorConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_editor_image",
            "{$type}_editor_video",
            "{$type}_editor_audio",
            "{$type}_editor_document",
            'image_extension_names',
            'image_max_size',
            "{$type}_editor_image_upload_form",
            "{$type}_editor_image_upload_number",
            'video_extension_names',
            'video_max_size',
            'video_max_time',
            "{$type}_editor_video_upload_form",
            "{$type}_editor_video_upload_number",
            'audio_extension_names',
            'audio_max_size',
            'audio_max_time',
            "{$type}_editor_audio_upload_form",
            "{$type}_editor_audio_upload_number",
            'document_extension_names',
            'document_max_size',
            "{$type}_editor_document_upload_form",
            "{$type}_editor_document_upload_number",
            'post_editor_title',
            'post_editor_title_view',
            'post_editor_title_required',
            'post_editor_title_length',
            'mention_status',
            "{$type}_editor_mention",
            "{$type}_editor_hashtag",
            'hashtag_status',
            'hashtag_format',
            "{$type}_editor_expand",
            "{$type}_editor_location",
            "{$type}_editor_anonymous",
            "{$type}_editor_extend",
            'post_editor_group',
            'post_editor_group_required',
            "{$type}_editor_content_length",
            'image_service',
            'video_service',
            'audio_service',
            'document_service',
            'map_service',
        ]);

        $imageUploadUrl = PluginHelper::fresnsPluginUrlByFskey($editorConfig['image_service']);
        $videoUploadUrl = PluginHelper::fresnsPluginUrlByFskey($editorConfig['video_service']);
        $audioUploadUrl = PluginHelper::fresnsPluginUrlByFskey($editorConfig['audio_service']);
        $documentUploadUrl = PluginHelper::fresnsPluginUrlByFskey($editorConfig['document_service']);

        // images
        $image['status'] = $editorConfig["{$type}_editor_image"] ? $rolePerm["{$type}_editor_image"] : false;
        $image['extensions'] = Str::lower($editorConfig['image_extension_names']);
        $image['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_IMAGE);
        $image['maxSize'] = (int) (empty($rolePerm['image_max_size']) ? $editorConfig['image_max_size'] : $rolePerm['image_max_size']);
        $image['maxTime'] = null;
        $image['uploadForm'] = $imageUploadUrl ? $editorConfig["{$type}_editor_image_upload_form"] : 'fresns';
        $image['uploadUrl'] = $imageUploadUrl;
        $image['uploadNumber'] = (int) (empty($rolePerm["{$type}_editor_image_upload_number"]) ? $editorConfig["{$type}_editor_image_upload_number"] : $rolePerm["{$type}_editor_image_upload_number"]);

        // videos
        $video['status'] = $editorConfig["{$type}_editor_video"] ? $rolePerm["{$type}_editor_video"] : false;
        $video['extensions'] = Str::lower($editorConfig['video_extension_names']);
        $video['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_VIDEO);
        $video['maxSize'] = (int) (empty($rolePerm['video_max_size']) ? $editorConfig['video_max_size'] : $rolePerm['video_max_size']);
        $video['maxTime'] = (int) (empty($rolePerm['video_max_time']) ? $editorConfig['video_max_time'] : $rolePerm['video_max_time']);
        $video['uploadForm'] = $videoUploadUrl ? $editorConfig["{$type}_editor_video_upload_form"] : 'fresns';
        $video['uploadUrl'] = $videoUploadUrl;
        $video['uploadNumber'] = (int) (empty($rolePerm["{$type}_editor_video_upload_number"]) ? $editorConfig["{$type}_editor_video_upload_number"] : $rolePerm["{$type}_editor_video_upload_number"]);

        // audios
        $audio['status'] = $editorConfig["{$type}_editor_audio"] ? $rolePerm["{$type}_editor_audio"] : false;
        $audio['extensions'] = Str::lower($editorConfig['audio_extension_names']);
        $audio['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_AUDIO);
        $audio['maxSize'] = (int) (empty($rolePerm['audio_max_size']) ? $editorConfig['audio_max_size'] : $rolePerm['audio_max_size']);
        $audio['maxTime'] = (int) (empty($rolePerm['audio_max_time']) ? $editorConfig['audio_max_time'] : $rolePerm['audio_max_time']);
        $audio['uploadForm'] = $audioUploadUrl ? $editorConfig["{$type}_editor_audio_upload_form"] : 'fresns';
        $audio['uploadUrl'] = $audioUploadUrl;
        $audio['uploadNumber'] = (int) (empty($rolePerm["{$type}_editor_audio_upload_number"]) ? $editorConfig["{$type}_editor_audio_upload_number"] : $rolePerm["{$type}_editor_audio_upload_number"]);

        // documents
        $document['status'] = $editorConfig["{$type}_editor_document"] ? $rolePerm["{$type}_editor_document"] : false;
        $document['extensions'] = Str::lower($editorConfig['document_extension_names']);
        $document['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_DOCUMENT);
        $document['maxSize'] = (int) (empty($rolePerm['document_max_size']) ? $editorConfig['document_max_size'] : $rolePerm['document_max_size']);
        $document['maxTime'] = null;
        $document['uploadForm'] = $documentUploadUrl ? $editorConfig["{$type}_editor_document_upload_form"] : 'fresns';
        $document['uploadUrl'] = $documentUploadUrl;
        $document['uploadNumber'] = (int) (empty($rolePerm["{$type}_editor_document_upload_number"]) ? $editorConfig["{$type}_editor_document_upload_number"] : $rolePerm["{$type}_editor_document_upload_number"]);

        // title
        if ($type == 'post') {
            $title['status'] = $editorConfig['post_editor_title'];
            $title['view'] = $editorConfig['post_editor_title_view'];
            $title['required'] = $editorConfig['post_editor_title_required'];
            $title['length'] = $editorConfig['post_editor_title_length'];

            $group['status'] = $editorConfig['post_editor_group'];
            $group['required'] = $editorConfig['post_editor_group_required'];
        } else {
            $title['status'] = false;
            $title['view'] = 2;
            $title['required'] = false;
            $title['length'] = 0;

            $group['status'] = false;
            $group['required'] = false;
        }

        // mention
        $mention['status'] = $editorConfig['mention_status'];
        $mention['display'] = $editorConfig["{$type}_editor_mention"];

        // hashtag
        $hashtag['status'] = $editorConfig['hashtag_status'];
        $hashtag['display'] = $editorConfig["{$type}_editor_hashtag"];
        $hashtag['format'] = $editorConfig['hashtag_format'];

        // extend
        $extend['status'] = $editorConfig["{$type}_editor_extend"];
        $extend['list'] = ExtendUtility::getEditorExtensions($type, $userId, $langTag);

        // toolbar
        $toolbar['sticker'] = ConfigHelper::fresnsConfigByItemKey("{$type}_editor_sticker");
        $toolbar['image'] = $image;
        $toolbar['video'] = $video;
        $toolbar['audio'] = $audio;
        $toolbar['document'] = $document;
        $toolbar['title'] = $title;
        $toolbar['mention'] = $mention;
        $toolbar['hashtag'] = $hashtag;
        $toolbar['extend'] = $extend;

        // location
        $location['status'] = $editorConfig["{$type}_editor_location"];
        $location['map'] = PluginHelper::fresnsPluginUrlByFskey($editorConfig['map_service']);

        // feature
        $feature['group'] = $group;
        $feature['location'] = $location;
        $feature['anonymous'] = $editorConfig["{$type}_editor_anonymous"];
        $feature['contentLength'] = $editorConfig["{$type}_editor_content_length"];

        $editor['toolbar'] = $toolbar;
        $editor['features'] = $feature;

        return $editor;
    }

    // get publish config by type(post or comment)
    public static function getPublishConfigByType(int $userId, string $type, ?string $langTag = null, ?string $timezone = null): array
    {
        $cacheKey = "fresns_publish_{$type}_config_{$userId}_{$langTag}";
        $cacheTag = 'fresnsUsers';

        $publishConfig = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($publishConfig)) {
            $rolePerm = PermissionUtility::getUserMainRole($userId)['permissions'];

            $user = PrimaryHelper::fresnsModelById('user', $userId);
            $account = PrimaryHelper::fresnsModelById('account', $user->account_id);

            $limitConfig = ConfigHelper::fresnsConfigByItemKeys([
                "{$type}_email_verify",
                "{$type}_phone_verify",
                "{$type}_real_name_verify",
                "{$type}_limit_status",
                "{$type}_limit_type",
                "{$type}_limit_period_start",
                "{$type}_limit_period_end",
                "{$type}_limit_cycle_start",
                "{$type}_limit_cycle_end",
                "{$type}_limit_rule",
                "{$type}_limit_tip",
                "{$type}_limit_whitelist",
            ], $langTag);

            $perm['draft'] = true;
            $perm['publish'] = $rolePerm["{$type}_publish"];
            $perm['review'] = $rolePerm["{$type}_review"];
            $perm['emailRequired'] = $limitConfig["{$type}_email_verify"] ? $limitConfig["{$type}_email_verify"] : $rolePerm["{$type}_email_verify"];
            $perm['phoneRequired'] = $limitConfig["{$type}_phone_verify"] ? $limitConfig["{$type}_phone_verify"] : $rolePerm["{$type}_phone_verify"];
            $perm['realNameRequired'] = $limitConfig["{$type}_real_name_verify"] ? $limitConfig["{$type}_real_name_verify"] : $rolePerm["{$type}_real_name_verify"];

            $checkLogCount = match ($type) {
                'post' => PostLog::where('user_id', $userId)->whereIn('state', [PostLog::STATE_DRAFT, PostLog::STATE_UNDER_REVIEW, PostLog::STATE_FAILURE])->count(),
                'comment' => CommentLog::where('user_id', $userId)->whereIn('state', [CommentLog::STATE_DRAFT, CommentLog::STATE_UNDER_REVIEW, CommentLog::STATE_FAILURE])->count(),
            };
            if ($checkLogCount >= $rolePerm["{$type}_draft_count"]) {
                $perm['draft'] = false;
            }

            $publishTip = $perm['publish'] ? null : ConfigUtility::getCodeMessage(36104, 'Fresns', $langTag);
            $emailTip = null;
            $phoneTip = null;
            $realNameTip = null;

            if ($perm['publish']) {
                if ($perm['emailRequired'] && empty($account->email)) {
                    $perm['publish'] = false;
                    $emailTip = ConfigUtility::getCodeMessage(36301, 'Fresns', $langTag);
                    $emailTip = "[{$emailTip}]";
                }

                if ($perm['phoneRequired'] && empty($account->phone)) {
                    $perm['publish'] = false;
                    $phoneTip = ConfigUtility::getCodeMessage(36302, 'Fresns', $langTag);
                    $phoneTip = "[{$phoneTip}]";
                }

                if ($perm['realNameRequired'] && ! $account->is_verify) {
                    $perm['publish'] = false;
                    $realNameTip = ConfigUtility::getCodeMessage(36303, 'Fresns', $langTag);
                    $realNameTip = "[{$realNameTip}]";
                }
            }

            $perm['tips'] = Arr::flatten(array_filter([$publishTip, $emailTip, $phoneTip, $realNameTip]));

            if ($limitConfig["{$type}_limit_status"]) {
                $checkWhiteList = PermissionUtility::checkUserRolePerm($userId, $limitConfig["{$type}_limit_whitelist"]);

                $limit['status'] = ! $checkWhiteList ? $limitConfig["{$type}_limit_status"] : false;
                $limit['isInTime'] = false;
                $limit['type'] = $limitConfig["{$type}_limit_type"];
                $limit['periodStart'] = $limitConfig["{$type}_limit_period_start"];
                $limit['periodEnd'] = $limitConfig["{$type}_limit_period_end"];
                $limit['periodStartFormat'] = $limitConfig["{$type}_limit_period_start"];
                $limit['periodEndFormat'] = $limitConfig["{$type}_limit_period_end"];
                $limit['cycleStart'] = $limitConfig["{$type}_limit_cycle_start"];
                $limit['cycleEnd'] = $limitConfig["{$type}_limit_cycle_end"];
                $limit['cycleStartFormat'] = $limitConfig["{$type}_limit_cycle_start"];
                $limit['cycleEndFormat'] = $limitConfig["{$type}_limit_cycle_end"];
                $limit['rule'] = $limitConfig["{$type}_limit_rule"];
                $limit['tip'] = $limitConfig["{$type}_limit_tip"];
            } else {
                $limit['status'] = $rolePerm["{$type}_limit_status"];
                $limit['isInTime'] = false;
                $limit['type'] = $rolePerm["{$type}_limit_type"];
                $limit['periodStart'] = $rolePerm["{$type}_limit_period_start"];
                $limit['periodEnd'] = $rolePerm["{$type}_limit_period_end"];
                $limit['periodStartFormat'] = $rolePerm["{$type}_limit_period_start"];
                $limit['periodEndFormat'] = $rolePerm["{$type}_limit_period_end"];
                $limit['cycleStart'] = $rolePerm["{$type}_limit_cycle_start"];
                $limit['cycleEnd'] = $rolePerm["{$type}_limit_cycle_end"];
                $limit['cycleStartFormat'] = $rolePerm["{$type}_limit_cycle_start"];
                $limit['cycleEndFormat'] = $rolePerm["{$type}_limit_cycle_end"];
                $limit['rule'] = $rolePerm["{$type}_limit_rule"];
                $limit['tip'] = ConfigUtility::getCodeMessage(36105, 'Fresns', $langTag);
            }

            $publish['perm'] = $perm;
            $publish['limit'] = $limit;

            $publishConfig = $publish;

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($publishConfig, $cacheKey, $cacheTag, null, $cacheTime);
        }

        $publishConfig['limit']['periodStartFormat'] = DateHelper::fresnsDateTimeByTimezone($publishConfig['limit']['periodStartFormat'], $timezone, $langTag);
        $publishConfig['limit']['periodEndFormat'] = DateHelper::fresnsDateTimeByTimezone($publishConfig['limit']['periodEndFormat'], $timezone, $langTag);
        $publishConfig['limit']['cycleStartFormat'] = DateHelper::fresnsTimeByTimezone($publishConfig['limit']['cycleStartFormat'], $timezone);
        $publishConfig['limit']['cycleEndFormat'] = DateHelper::fresnsTimeByTimezone($publishConfig['limit']['cycleEndFormat'], $timezone);

        // is in time
        if ($publishConfig['limit']['status']) {
            $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
            $newDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dbDateTime);

            switch ($publishConfig['limit']['type']) {
                case 1:
                    // period
                    // Y-m-d H:i:s
                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($publishConfig['limit']['periodStart'])));
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s', strtotime($publishConfig['limit']['periodEnd'])));
                    break;

                case 2:
                    // cycle
                    // H:i
                    $dbDate = date('Y-m-d', strtotime($dbDateTime));
                    $cycleStart = "{$dbDate} {$publishConfig['limit']['cycleStart']}:00"; // Y-m-d H:i:s
                    $cycleEnd = "{$dbDate} {$publishConfig['limit']['cycleEnd']}:00"; // Y-m-d H:i:s

                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $cycleStart); // 2022-07-02 22:30:00
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', $cycleEnd); // 2022-07-02 08:30:00

                    // 2022-07-02 22:30:00 > 2022-07-02 08:30:00
                    if ($periodStart->gt($periodEnd)) {
                        // previous day 2022-07-01 08:30:00
                        $periodStart = $periodStart->subDay();
                    }

                    // 2022-07-02 08:30:00 < 2022-07-02 22:30:00
                    if ($periodEnd->lt($periodStart)) {
                        // next day 2022-07-03 08:30:00
                        $periodEnd = $periodEnd->addDay();
                    }
                    break;
            }

            // check time
            $isInTime = $newDateTime->between($periodStart, $periodEnd);

            if ($isInTime) {
                $publishConfig['limit']['isInTime'] = true;
            }
        }

        return $publishConfig;
    }

    // get edit config by type(post or comment)
    public static function getEditConfigByType(string $type): array
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_edit",
            "{$type}_edit_time_limit",
            "{$type}_edit_sticky_limit",
            "{$type}_edit_digest_limit",
        ]);

        $edit['function'] = $editConfig["{$type}_edit"];
        $edit['timeLimit'] = $editConfig["{$type}_edit_time_limit"];
        $edit['stickyLimit'] = $editConfig["{$type}_edit_sticky_limit"];
        $edit['digestLimit'] = $editConfig["{$type}_edit_digest_limit"];

        return $edit;
    }
}

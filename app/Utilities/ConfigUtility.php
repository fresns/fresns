<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Models\Account;
use App\Models\CodeMessage;
use App\Models\CommentLog;
use App\Models\Config;
use App\Models\File;
use App\Models\Language;
use App\Models\PluginUsage;
use App\Models\PostLog;
use App\Models\SessionLog;
use App\Models\User;

class ConfigUtility
{
    // add config items
    public static function addFresnsConfigItems(array $fresnsConfigItems)
    {
        foreach ($fresnsConfigItems as $item) {
            $config = Config::where('item_key', $item['item_key'])->first();
            if (empty($config)) {
                Config::create($item);

                if ($item['is_multilingual'] ?? null) {
                    $fresnsLangItems = [
                        'table_name' => 'configs',
                        'table_column' => 'item_value',
                        'table_id' => null,
                        'table_key' => $item['item_key'],
                        'language_values' => $item['language_values'],
                    ];
                    ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
                }
            }
        }
    }

    // remove config items
    public static function removeFresnsConfigItems(array $fresnsConfigKeys)
    {
        foreach ($fresnsConfigKeys as $item) {
            Config::where('item_key', $item)->where('is_custom', 1)->forceDelete();
        }
    }

    // change config items
    public static function changeFresnsConfigItems(array $fresnsConfigItems)
    {
        foreach ($fresnsConfigItems as $item) {
            Config::updateOrCreate([
                'item_key' => $item['item_key'],
            ],
                collect($item)->only('item_key', 'item_value', 'item_type', 'item_tag', 'is_multilingual', 'is_api')->toArray()
            );

            if ($item['is_multilingual'] ?? null) {
                $fresnsLangItems = [
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_id' => null,
                    'table_key' => $item['item_key'],
                    'language_values' => $item['language_values'],
                ];
                ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
            }
        }
    }

    // change language items
    public static function changeFresnsLanguageItems($fresnsLangItems)
    {
        foreach ($fresnsLangItems['language_values'] ?? [] as $key => $value) {
            $item = $fresnsLangItems;
            $item['lang_tag'] = $key;
            $item['lang_content'] = $value;

            unset($item['language_values']);

            Language::updateOrCreate($item);
        }
    }

    // get code message
    public static function getCodeMessage(int $code, ?string $unikey = null, ?string $langTag = null)
    {
        $unikey = $unikey ?: 'Fresns';

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $message = CodeMessage::where('plugin_unikey', $unikey)->where('code', $code)->where('lang_tag', $langTag)->value('message');

        return $message ?? 'Unknown Error';
    }

    // get login error count
    public static function getLoginErrorCount(int $accountId, ?int $userId = null): int
    {
        $sessionLog = SessionLog::whereIn('type', [2, 5, 8])
            ->whereIn('object_result', [1, 2])
            ->where('account_id', $accountId)
            ->where('created_at', '>=', now()->subHour());

        if (! empty($userId)) {
            $sessionLog->where('user_id', $userId);
        }

        $errorCount = $sessionLog->count();

        return $errorCount;
    }

    // get editor config by type(post or comment)
    public static function getEditorConfigByType(int $userId, string $type, ?string $langTag = null): array
    {
        $rolePerm = PermissionUtility::getUserMainRolePerm($userId);
        $editorConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_editor_image",
            "{$type}_editor_video",
            "{$type}_editor_audio",
            "{$type}_editor_document",
            'image_extension_names',
            'image_max_size',
            "{$type}_editor_image_upload_number",
            'video_extension_names',
            'video_max_size',
            'video_max_time',
            "{$type}_editor_video_upload_number",
            'audio_extension_names',
            'audio_max_size',
            'audio_max_time',
            "{$type}_editor_audio_upload_number",
            'document_extension_names',
            'document_max_size',
            "{$type}_editor_document_upload_number",
            'post_editor_title',
            'post_editor_title_view',
            'post_editor_title_required',
            'post_editor_title_length',
            "{$type}_editor_mention",
            "{$type}_editor_hashtag",
            'hashtag_show',
            "{$type}_editor_expand",
            "{$type}_editor_location",
            "{$type}_editor_anonymous",
            "{$type}_editor_extend",
            'post_editor_group',
            'post_editor_group_required',
            "{$type}_editor_content_length",
        ]);

        // images
        $image['status'] = $editorConfig["{$type}_editor_image"] ? $rolePerm["{$type}_editor_image"] : false;
        $image['extensions'] = $editorConfig['image_extension_names'];
        $image['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_IMAGE);
        $image['maxSize'] = $rolePerm['image_max_size'] ?? $editorConfig['image_max_size'];
        $image['uploadNumber'] = $rolePerm["{$type}_editor_image_upload_number"] ?? $editorConfig["{$type}_editor_image_upload_number"];

        // videos
        $video['status'] = $editorConfig["{$type}_editor_video"] ? $rolePerm["{$type}_editor_video"] : false;
        $video['extensions'] = $editorConfig['video_extension_names'];
        $video['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_VIDEO);
        $video['maxSize'] = $rolePerm['video_max_size'] ?? $editorConfig['video_max_size'];
        $video['maxTime'] = $rolePerm['video_max_time'] ?? $editorConfig['video_max_time'];
        $video['uploadNumber'] = $rolePerm["{$type}_editor_video_upload_number"] ?? $editorConfig["{$type}_editor_video_upload_number"];

        // audios
        $audio['status'] = $editorConfig["{$type}_editor_audio"] ? $rolePerm["{$type}_editor_audio"] : false;
        $audio['extensions'] = $editorConfig['audio_extension_names'];
        $audio['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_AUDIO);
        $audio['maxSize'] = $rolePerm['audio_max_size'] ?? $editorConfig['audio_max_size'];
        $audio['maxTime'] = $rolePerm['audio_max_time'] ?? $editorConfig['audio_max_time'];
        $audio['uploadNumber'] = $rolePerm["{$type}_editor_audio_upload_number"] ?? $editorConfig["{$type}_editor_audio_upload_number"];

        // documents
        $document['status'] = $editorConfig["{$type}_editor_document"] ? $rolePerm["{$type}_editor_document"] : false;
        $document['extensions'] = $editorConfig['document_extension_names'];
        $document['inputAccept'] = FileHelper::fresnsFileAcceptByType(File::TYPE_DOCUMENT);
        $document['maxSize'] = $rolePerm['document_max_size'] ?? $editorConfig['document_max_size'];
        $document['uploadNumber'] = $rolePerm["{$type}_editor_document_upload_number"] ?? $editorConfig["{$type}_editor_document_upload_number"];

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

        // hashtag
        $hashtag['status'] = $editorConfig["{$type}_editor_hashtag"];
        $hashtag['showMode'] = $editorConfig['hashtag_show'];

        // extend
        $extendType = match ($type) {
            'post' => 1,
            'comment' => 2,
        };
        $extend['status'] = $editorConfig["{$type}_editor_extend"];
        $extend['list'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_EDITOR, null, $extendType, $userId, $langTag);

        // toolbar
        $toolbar['sticker'] = ConfigHelper::fresnsConfigByItemKey("{$type}_editor_sticker");
        $toolbar['image'] = $image;
        $toolbar['video'] = $video;
        $toolbar['audio'] = $audio;
        $toolbar['document'] = $document;
        $toolbar['title'] = $title;
        $toolbar['mention'] = $editorConfig["{$type}_editor_mention"];
        $toolbar['hashtag'] = $hashtag;
        $toolbar['extend'] = $extend;

        // location
        $location['status'] = $editorConfig["{$type}_editor_location"];
        $location['maps'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_MAP, null, null, null, $langTag);

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
        $rolePerm = PermissionUtility::getUserMainRolePerm($userId);

        $accountId = User::where('id', $userId)->value('account_id');
        $account = Account::where('id', $accountId)->first();

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
            "{$type}_limit_whitelist",
        ], $langTag);

        $perm['draft'] = true;
        $perm['publish'] = $rolePerm["{$type}_publish"];
        $perm['review'] = $rolePerm["{$type}_review"];
        $perm['emailRequired'] = $limitConfig["{$type}_email_verify"] ? $limitConfig["{$type}_email_verify"] : $rolePerm["{$type}_email_verify"];
        $perm['phoneRequired'] = $limitConfig["{$type}_phone_verify"] ? $limitConfig["{$type}_phone_verify"] : $rolePerm["{$type}_phone_verify"];
        $perm['realNameRequired'] = $limitConfig["{$type}_real_name_verify"] ? $limitConfig["{$type}_real_name_verify"] : $rolePerm["{$type}_real_name_verify"];

        $checkLogCount = match ($type) {
            'post' => PostLog::where('user_id', $userId)->whereIn('state', [1, 2, 4])->count(),
            'comment' => CommentLog::where('user_id', $userId)->whereIn('state', [1, 2, 4])->count(),
        };
        if ($checkLogCount >= $rolePerm["{$type}_draft_count"]) {
            $perm['draft'] = false;
        }

        $publishTip = null;
        $emailTip = null;
        $phoneTip = null;
        $realNameTip = null;

        if ($perm['publish']) {
            if ($perm['emailRequired'] && empty($account->email)) {
                $perm['publish'] = false;
                $publishTip = ConfigUtility::getCodeMessage(36104, 'Fresns', $langTag);
            }

            if ($perm['phoneRequired'] && empty($account->phone)) {
                $perm['publish'] = false;
                $phoneTip = ConfigUtility::getCodeMessage(36302, 'Fresns', $langTag);
            }

            if ($perm['realNameRequired'] && ! $account->is_verify) {
                $perm['publish'] = false;
                $realNameTip = ConfigUtility::getCodeMessage(36303, 'Fresns', $langTag);
            }
        }

        $perm['tips'] = array_filter([$publishTip, $emailTip, $phoneTip, $realNameTip]);

        if ($limitConfig["{$type}_limit_status"]) {
            $checkWhiteList = PermissionUtility::checkUserRolePerm($userId, $limitConfig["{$type}_limit_whitelist"]);

            $limit['status'] = ! $checkWhiteList ? $limitConfig["{$type}_limit_status"] : false;
            $limit['type'] = $limitConfig["{$type}_limit_type"];
            $limit['periodStart'] = $limitConfig["{$type}_limit_period_start"];
            $limit['periodEnd'] = $limitConfig["{$type}_limit_period_end"];
            $limit['periodStartFormat'] = DateHelper::fresnsDateTimeByTimezone($limitConfig["{$type}_limit_period_start"], $timezone, $langTag);
            $limit['periodEndFormat'] = DateHelper::fresnsDateTimeByTimezone($limitConfig["{$type}_limit_period_end"], $timezone, $langTag);
            $limit['cycleStart'] = $limitConfig["{$type}_limit_cycle_start"];
            $limit['cycleEnd'] = $limitConfig["{$type}_limit_cycle_end"];
            $limit['cycleStartFormat'] = DateHelper::fresnsTimeByTimezone($limitConfig["{$type}_limit_cycle_start"], $timezone);
            $limit['cycleEndFormat'] = DateHelper::fresnsTimeByTimezone($limitConfig["{$type}_limit_cycle_end"], $timezone);
            $limit['rule'] = $limitConfig["{$type}_limit_rule"];
            $limit['tip'] = $limitConfig["{$type}_limit_tip"];
        } else {
            $limit['status'] = $rolePerm["{$type}_limit_status"];
            $limit['type'] = $rolePerm["{$type}_limit_type"];
            $limit['periodStart'] = $rolePerm["{$type}_limit_period_start"];
            $limit['periodEnd'] = $rolePerm["{$type}_limit_period_end"];
            $limit['periodStartFormat'] = DateHelper::fresnsDateTimeByTimezone($rolePerm["{$type}_limit_period_start"], $timezone, $langTag);
            $limit['periodEndFormat'] = DateHelper::fresnsDateTimeByTimezone($rolePerm["{$type}_limit_period_end"], $timezone, $langTag);
            $limit['cycleStart'] = $rolePerm["{$type}_limit_cycle_start"];
            $limit['cycleEnd'] = $rolePerm["{$type}_limit_cycle_end"];
            $limit['cycleStartFormat'] = DateHelper::fresnsTimeByTimezone($rolePerm["{$type}_limit_cycle_start"], $timezone);
            $limit['cycleEndFormat'] = DateHelper::fresnsTimeByTimezone($rolePerm["{$type}_limit_cycle_end"], $timezone);
            $limit['rule'] = $rolePerm["{$type}_limit_rule"];
            $limit['tip'] = ConfigUtility::getCodeMessage(36105, 'Fresns', $langTag);
        }

        $publish['perm'] = $perm;
        $publish['limit'] = $limit;

        return $publish;
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

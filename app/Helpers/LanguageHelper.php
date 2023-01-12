<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Language;
use App\Models\Seo;

class LanguageHelper
{
    /**
     * Get language values based on multilingual columns.
     *
     * @param  string  $tableName
     * @param  string  $tableColumn
     * @param  int  $tableId
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsLanguageByTableId(string $tableName, string $tableColumn, int $tableId, ?string $langTag = null)
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}";
        $cacheTags = match ($tableName) {
            'configs' => ['fresnsLanguages', 'fresnsConfigLanguages'],
            'users' => ['fresnsLanguages', 'fresnsUsers', 'fresnsUserData'],
            'groups' => ['fresnsLanguages', 'fresnsGroups', 'fresnsGroupData'],
            'hashtags' => ['fresnsLanguages', 'fresnsHashtags', 'fresnsHashtagData'],
            'posts' => ['fresnsLanguages', 'fresnsPosts', 'fresnsPostData'],
            'post_appends' => ['fresnsLanguages', 'fresnsPosts', 'fresnsPostData'],
            'comments' => ['fresnsLanguages', 'fresnsComments', 'fresnsCommentData'],
            'comment_appends' => ['fresnsLanguages', 'fresnsComments', 'fresnsCommentData'],
            'plugin_usages' => ['fresnsLanguages', 'fresnsPluginUsageLanguages'],
            'extends' => ['fresnsLanguages', 'fresnsExtends'],
            'archives' => ['fresnsLanguages', 'fresnsArchives'],
            'operations' => ['fresnsLanguages', 'fresnsOperations'],
            'roles' => ['fresnsLanguages', 'fresnsRoleLanguages'],
            'stickers' => ['fresnsLanguages', 'fresnsStickerLanguages'],
            'notifications' => ['fresnsLanguages', 'fresnsNotificationLanguages'],
            default => 'fresnsUnknownLanguages',
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $langContent = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($langContent)) {
            if (empty($langTag)) {
                $languageArr = Language::where([
                    'table_name' => $tableName,
                    'table_column' => $tableColumn,
                    'table_id' => $tableId,
                ])->get()->toArray();

                if ($languageArr->isEmpty()) {
                    $langContent = [];
                } else {
                    foreach ($languageArr as $language) {
                        $item['langTag'] = $language['lang_tag'];
                        $item['langContent'] = $language['lang_content'];
                        $itemArr[] = $item;
                    }
                    $langContent = $itemArr;
                }
            } else {
                $langContent = Language::where([
                    'table_name' => $tableName,
                    'table_column' => $tableColumn,
                    'table_id' => $tableId,
                    'lang_tag' => $langTag,
                ])->first()->lang_content ?? null;
            }

            CacheHelper::put($langContent, $cacheKey, $cacheTags);
        }

        return $langContent;
    }

    /**
     * Get language values based on multilingual table key.
     *
     * @param  string  $tableKey
     * @param  string  $langTag
     * @return array
     */
    public static function fresnsLanguageByTableKey(string $tableKey, ?string $itemType = null, ?string $langTag = null)
    {
        $itemType = $itemType ?: 'string';

        if (empty($langTag)) {
            $languageArr = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
            ])->get();

            if ($languageArr->isEmpty()) {
                return null;
            }

            foreach ($languageArr as $language) {
                $item['langTag'] = $language['lang_tag'];
                $item['langContent'] = $language->formatConfigItemValue($itemType);
                $itemArr[] = $item;
            }

            $langContent = $itemArr;
        } else {
            $langContent = Language::where([
                'table_name' => 'configs',
                'table_column' => 'item_value',
                'table_key' => $tableKey,
                'lang_tag' => $langTag,
            ])->first()?->formatConfigItemValue($itemType);
        }

        return $langContent;
    }

    // get fresns seo language data
    public static function fresnsLanguageSeoDataById(string $type, int $id, ?string $langTag = null)
    {
        $cacheKey = "fresns_seo_{$type}_{$id}";
        $cacheTags = match ($type) {
            'user' => ['fresnsUsers', 'fresnsUserData'],
            'group' => ['fresnsGroups', 'fresnsGroupData'],
            'hashtag' => ['fresnsHashtags', 'fresnsHashtagData'],
            'post' => ['fresnsPosts', 'fresnsPostData'],
            'comment' => ['fresnsComments', 'fresnsCommentData'],
        };

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return null;
        }

        $seoData = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($seoData)) {
            $usageType = match ($type) {
                'user' => Seo::TYPE_USER,
                'group' => Seo::TYPE_GROUP,
                'hashtag' => Seo::TYPE_HASHTAG,
                'post' => Seo::TYPE_POST,
                'comment' => Seo::TYPE_COMMENT,
            };

            $seoData = Seo::where('usage_type', $usageType)->where('usage_id', $id)->get();

            CacheHelper::put($seoData, $cacheKey, $cacheTags);
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $langContent = $seoData->where('lang_tag', $langTag)->first();

        return $langContent ?? $seoData->first();
    }
}

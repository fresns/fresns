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
    // Get language values based on multilingual columns
    public static function fresnsLanguageByTableId(string $tableName, string $tableColumn, int $tableId, ?string $langTag = null): mixed
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_{$tableName}_{$tableColumn}_{$tableId}_{$langTag}";
        $cacheTags = match ($tableName) {
            'configs' => ['fresnsLanguages', 'fresnsConfigs'],
            'users' => ['fresnsLanguages', 'fresnsUsers'],
            'groups' => ['fresnsLanguages', 'fresnsGroups'],
            'hashtags' => ['fresnsLanguages', 'fresnsHashtags'],
            'posts' => ['fresnsLanguages', 'fresnsPosts'],
            'post_appends' => ['fresnsLanguages', 'fresnsPosts'],
            'comments' => ['fresnsLanguages', 'fresnsComments'],
            'comment_appends' => ['fresnsLanguages', 'fresnsComments'],
            'plugin_usages' => ['fresnsLanguages', 'fresnsExtensions'],
            'extends' => ['fresnsLanguages', 'fresnsExtensions'],
            'archives' => ['fresnsLanguages', 'fresnsExtensions'],
            'operations' => ['fresnsLanguages', 'fresnsExtensions'],
            'roles' => ['fresnsLanguages', 'fresnsConfigs'],
            'stickers' => ['fresnsLanguages', 'fresnsConfigs'],
            'notifications' => ['fresnsLanguages', 'fresnsUsers'],
            default => 'fresnsLanguages',
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

    // Get language values based on multilingual table key
    public static function fresnsLanguageByTableKey(string $tableKey, ?string $itemType = null, ?string $langTag = null): mixed
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
    public static function fresnsLanguageSeoDataById(string $type, int $id, ?string $langTag = null): ?Seo
    {
        $cacheKey = "fresns_seo_{$type}_{$id}";
        $cacheTags = match ($type) {
            'user' => ['fresnsSeo', 'fresnsUsers'],
            'group' => ['fresnsSeo', 'fresnsGroups'],
            'hashtag' => ['fresnsSeo', 'fresnsHashtags'],
            'post' => ['fresnsSeo', 'fresnsPosts'],
            'comment' => ['fresnsSeo', 'fresnsComments'],
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

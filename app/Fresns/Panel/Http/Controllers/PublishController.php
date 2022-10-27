<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\DateHelper;
use App\Models\Config;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\Role;
use Illuminate\Http\Request;

class PublishController extends Controller
{
    public function postShow()
    {
        // config keys
        $configKeys = [
            'post_email_verify',
            'post_phone_verify',
            'post_real_name_verify',
            'post_limit_status',
            'post_limit_type',
            'post_limit_period_start',
            'post_limit_period_end',
            'post_limit_cycle_start',
            'post_limit_cycle_end',
            'post_limit_rule',
            'post_limit_tip',
            'post_limit_whitelist',
            'post_edit',
            'post_edit_time_limit',
            'post_edit_sticky_limit',
            'post_edit_digest_limit',
            'post_editor_service',
            'post_editor_group',
            'post_editor_title',
            'post_editor_sticker',
            'post_editor_image',
            'post_editor_video',
            'post_editor_audio',
            'post_editor_document',
            'post_editor_mention',
            'post_editor_hashtag',
            'post_editor_extend',
            'post_editor_location',
            'post_editor_anonymous',
            'post_editor_image_upload_form',
            'post_editor_video_upload_form',
            'post_editor_audio_upload_form',
            'post_editor_document_upload_form',
            'post_editor_image_upload_number',
            'post_editor_video_upload_number',
            'post_editor_audio_upload_number',
            'post_editor_document_upload_number',
            'post_editor_group_required',
            'post_editor_title_view',
            'post_editor_title_required',
            'post_editor_title_length',
            'post_editor_content_length',
            'post_editor_brief_length',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $languages = Language::ofConfig()->where('table_key', 'post_limit_tip')->get();

        $ruleTimezone = 'UTC'.DateHelper::fresnsDatabaseTimezone();

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('editor', $plugin->scene);
        });

        $roles = Role::all();

        return view('FsView::operations.publish-post', compact('params', 'ruleTimezone', 'languages', 'plugins', 'roles'));
    }

    public function postUpdate(Request $request)
    {
        $configKeys = [
            'post_email_verify',
            'post_phone_verify',
            'post_real_name_verify',
            'post_limit_status',
            'post_limit_type',
            'post_limit_period_start',
            'post_limit_period_end',
            'post_limit_cycle_start',
            'post_limit_cycle_end',
            'post_limit_rule',
            'post_limit_tip',
            'post_limit_whitelist',
            'post_edit',
            'post_edit_time_limit',
            'post_edit_sticky_limit',
            'post_edit_digest_limit',
            'post_editor_service',
            'post_editor_group',
            'post_editor_title',
            'post_editor_sticker',
            'post_editor_image',
            'post_editor_video',
            'post_editor_audio',
            'post_editor_document',
            'post_editor_mention',
            'post_editor_hashtag',
            'post_editor_extend',
            'post_editor_location',
            'post_editor_anonymous',
            'post_editor_image_upload_form',
            'post_editor_video_upload_form',
            'post_editor_audio_upload_form',
            'post_editor_document_upload_form',
            'post_editor_image_upload_number',
            'post_editor_video_upload_number',
            'post_editor_audio_upload_number',
            'post_editor_document_upload_number',
            'post_editor_group_required',
            'post_editor_title_view',
            'post_editor_title_required',
            'post_editor_title_length',
            'post_editor_content_length',
            'post_editor_brief_length',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();
        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        foreach ($request->post_limit_tip as $langTag => $content) {
            $language = Language::tableName('configs')
                ->where('table_key', 'post_limit_tip')
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_key' => 'post_limit_tip',
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->updateSuccess();
    }

    public function commentShow()
    {
        // config keys
        $configKeys = [
            'comment_email_verify',
            'comment_phone_verify',
            'comment_real_name_verify',
            'comment_limit_status',
            'comment_limit_type',
            'comment_limit_period_start',
            'comment_limit_period_end',
            'comment_limit_cycle_start',
            'comment_limit_cycle_end',
            'comment_limit_rule',
            'comment_limit_tip',
            'comment_limit_whitelist',
            'comment_edit',
            'comment_edit_time_limit',
            'comment_edit_sticky_limit',
            'comment_edit_digest_limit',
            'comment_editor_service',
            'comment_editor_sticker',
            'comment_editor_image',
            'comment_editor_video',
            'comment_editor_audio',
            'comment_editor_document',
            'comment_editor_mention',
            'comment_editor_hashtag',
            'comment_editor_extend',
            'comment_editor_location',
            'comment_editor_anonymous',
            'comment_editor_image_upload_form',
            'comment_editor_video_upload_form',
            'comment_editor_audio_upload_form',
            'comment_editor_document_upload_form',
            'comment_editor_image_upload_number',
            'comment_editor_video_upload_number',
            'comment_editor_audio_upload_number',
            'comment_editor_document_upload_number',
            'comment_editor_content_length',
            'comment_editor_brief_length',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $languages = Language::ofConfig()->where('table_key', 'comment_limit_tip')->get();

        $ruleTimezone = 'UTC'.DateHelper::fresnsDatabaseTimezone();

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('editor', $plugin->scene);
        });

        $roles = Role::all();

        return view('FsView::operations.publish-comment', compact('params', 'ruleTimezone', 'languages', 'plugins', 'roles'));
    }

    public function commentUpdate(Request $request)
    {
        $configKeys = [
            'comment_email_verify',
            'comment_phone_verify',
            'comment_real_name_verify',
            'comment_limit_status',
            'comment_limit_type',
            'comment_limit_period_start',
            'comment_limit_period_end',
            'comment_limit_cycle_start',
            'comment_limit_cycle_end',
            'comment_limit_rule',
            'comment_limit_tip',
            'comment_limit_whitelist',
            'comment_edit',
            'comment_edit_time_limit',
            'comment_edit_sticky_limit',
            'comment_edit_digest_limit',
            'comment_editor_service',
            'comment_editor_sticker',
            'comment_editor_image',
            'comment_editor_video',
            'comment_editor_audio',
            'comment_editor_document',
            'comment_editor_mention',
            'comment_editor_hashtag',
            'comment_editor_extend',
            'comment_editor_location',
            'comment_editor_anonymous',
            'comment_editor_image_upload_form',
            'comment_editor_video_upload_form',
            'comment_editor_audio_upload_form',
            'comment_editor_document_upload_form',
            'comment_editor_image_upload_number',
            'comment_editor_video_upload_number',
            'comment_editor_audio_upload_number',
            'comment_editor_document_upload_number',
            'comment_editor_content_length',
            'comment_editor_brief_length',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        foreach ($request->comment_limit_tip as $langTag => $content) {
            $language = Language::tableName('configs')
                ->where('table_id', $config->id)
                ->where('table_key', 'comment_limit_tip')
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_key' => 'comment_limit_tip',
                    'table_id' => $config->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->updateSuccess();
    }
}

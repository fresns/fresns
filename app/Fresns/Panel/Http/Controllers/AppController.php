<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppController extends Controller
{
    // path index
    public function pathIndex()
    {
        // config keys
        $configKeys = [
            'site_url',
            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_post_path',
            'website_comment_path',
            'website_user_detail_path',
            'website_group_detail_path',
            'website_hashtag_detail_path',
            'website_post_detail_path',
            'website_comment_detail_path',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $siteUrl = $params['site_url'];
        $siteUrl = rtrim($siteUrl, '/');

        return view('FsView::clients.paths', compact('params', 'siteUrl'));
    }

    // path update
    public function pathUpdate(Request $request)
    {
        // config keys
        $configKeys = [
            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_post_path',
            'website_comment_path',
            'website_user_detail_path',
            'website_group_detail_path',
            'website_hashtag_detail_path',
            'website_post_detail_path',
            'website_comment_detail_path',
        ];

        // system reserved
        $pathKeys = [
            'fresns',
            'location',
            'notifications',
            'conversations',
            'messages',
            'drafts',
        ];

        $rules = [];
        $messages = [];
        foreach ($configKeys as $key) {
            $rules[$key] = ['required', 'regex:/^[a-z]+$/i'];
            $messages["$key.required"] = __('FsLang::tips.website_path_empty_error');
            $messages["$key.regex"] = __('FsLang::tips.website_path_format_error');

            if (in_array($request->{$key}, $pathKeys)) {
                return back()->with('failure', sprintf(__('FsLang::tips.website_path_reserved_error').' -> ', $key));
            }
        }

        $data = $request->only($configKeys);

        $validate = validator($data, $rules, $messages);

        if (! $validate->passes()) {
            return back()->with('failure', $validate->errors()->first());
        }

        $data = array_unique($data);

        if (count($configKeys) !== count($data)) {
            return back()->with('failure', __('FsLang::tips.website_path_unique_error'));
        }

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }

    // basic index
    public function basicIndex()
    {
        // config keys
        $configKeys = [
            'website_cookie_prefix',
            'website_stat_code',
            'website_stat_position',
            'site_china_mode',
            'china_icp_filing',
            'china_icp_license',
            'china_mps_filing',
            'china_broadcasting_license',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $associationFile = public_path('apple-app-site-association');

        $appleAppSiteAssociation = '';
        if (file_exists($associationFile)) {
            $appleAppSiteAssociation = file_get_contents($associationFile);
        }

        return view('FsView::clients.basic', compact('params', 'appleAppSiteAssociation'));
    }

    // basic update
    public function basicUpdate(Request $request)
    {
        // config keys
        $configKeys = [
            'website_cookie_prefix',
            'website_stat_code',
            'website_stat_position',
            'site_china_mode',
            'china_icp_filing',
            'china_icp_license',
            'china_mps_filing',
            'china_broadcasting_license',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            if ($configKey == 'website_cookie_prefix') {
                $websiteCookiePrefix = Str::of($request->get('website_cookie_prefix'))->trim();

                if (! Str::startsWith($websiteCookiePrefix, 'fresns_')) {
                    $request->$configKey = 'fresns_'.$websiteCookiePrefix;
                }
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        if ($request->appleAppSiteAssociation) {
            $associationJson = json_decode($request->appleAppSiteAssociation, true);

            $associationFile = public_path('apple-app-site-association');

            $associationContent = json_encode($associationJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
            file_put_contents($associationFile, $associationContent);
        }

        return $this->updateSuccess();
    }

    // status index
    public function statusIndex()
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

        $client = $statusJson['client'] ?? [];

        return view('FsView::clients.status', compact('statusJson', 'client'));
    }

    // status update
    public function statusUpdate(Request $request)
    {
        $statusJson = [
            'name' => 'Fresns',
            'version' => AppHelper::VERSION,
            'activate' => (bool) $request->activate,
            'deactivateDescribe' => $request->deactivateDescribe,
            'client' => $request->client,
        ];

        $defaultLangTag = ConfigHelper::fresnsConfigDefaultLangTag();

        $statusJson['deactivateDescribe']['default'] = $request->deactivateDescribe[$defaultLangTag] ?? array_values($request->deactivateDescribe)[0] ?? '';

        $statusJson['client']['mobile']['ios']['describe']['default'] = $request->client['mobile']['ios']['describe'][$defaultLangTag] ?? array_values($request->client['mobile']['ios']['describe'])[0] ?? '';
        $statusJson['client']['mobile']['android']['describe']['default'] = $request->client['mobile']['android']['describe'][$defaultLangTag] ?? array_values($request->client['mobile']['android']['describe'])[0] ?? '';

        $statusJson['client']['tablet']['ios']['describe']['default'] = $request->client['tablet']['ios']['describe'][$defaultLangTag] ?? array_values($request->client['tablet']['ios']['describe'])[0] ?? '';
        $statusJson['client']['tablet']['android']['describe']['default'] = $request->client['tablet']['android']['describe'][$defaultLangTag] ?? array_values($request->client['tablet']['android']['describe'])[0] ?? '';

        $statusJson['client']['desktop']['macos']['describe']['default'] = $request->client['desktop']['macos']['describe'][$defaultLangTag] ?? array_values($request->client['desktop']['macos']['describe'])[0] ?? '';
        $statusJson['client']['desktop']['windows']['describe']['default'] = $request->client['desktop']['windows']['describe'][$defaultLangTag] ?? array_values($request->client['desktop']['windows']['describe'])[0] ?? '';
        $statusJson['client']['desktop']['linux']['describe']['default'] = $request->client['desktop']['linux']['describe'][$defaultLangTag] ?? array_values($request->client['desktop']['linux']['describe'])[0] ?? '';

        $statusJsonFile = public_path('status.json');

        $editContent = json_encode($statusJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
        file_put_contents($statusJsonFile, $editContent);

        return $this->updateSuccess();
    }
}

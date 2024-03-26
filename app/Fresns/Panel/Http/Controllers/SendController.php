<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use App\Models\Config;
use Illuminate\Http\Request;

class SendController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'send_email_service',
            'send_sms_service',
            'send_sms_default_code',
            'send_sms_supported_codes',
            'ios_notifications_service',
            'android_notifications_service',
            'desktop_notifications_service',
            'verifycode_template1',
            'verifycode_template2',
            'verifycode_template3',
            'verifycode_template4',
            'verifycode_template5',
            'verifycode_template6',
            'verifycode_template7',
            'verifycode_template8',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $params['send_sms_supported_codes'] = join(PHP_EOL, $params['send_sms_supported_codes']);

        $plugins = App::all();
        $emailPlugins = $plugins->filter(function ($plugin) {
            return in_array('sendEmail', $plugin->panel_usages);
        });
        $smsPlugins = $plugins->filter(function ($plugin) {
            return in_array('sendSms', $plugin->panel_usages);
        });
        $appPlugins = $plugins->filter(function ($plugin) {
            return in_array('appNotifications', $plugin->panel_usages);
        });

        return view('FsView::systems.send', compact('params', 'emailPlugins', 'smsPlugins', 'appPlugins'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'send_email_service',
            'send_sms_service',
            'send_sms_default_code',
            'send_sms_supported_codes',
            'ios_notifications_service',
            'android_notifications_service',
            'desktop_notifications_service',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            $value = $request->$configKey;
            if ($configKey == 'send_sms_supported_codes') {
                $value = explode("\r\n", $request->send_sms_supported_codes);
            }

            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }

    public function updateEmail($itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (empty($config)) {
            return back()->with('failure', $itemKey.' Not Available');
        }

        $verifyCodeTemplate = $config->item_value;

        $verifyCodeTemplate['email']['status'] = (bool) $request->is_enabled;

        foreach ($request->titles as $langTag => $title) {
            $verifyCodeTemplate['email']['templates'][$langTag] = [
                'title' => $title,
                'content' => $request->contents[$langTag],
            ];
        }

        $config->item_value = $verifyCodeTemplate;
        $config->save();

        return redirect(route('panel.send.index').'#templates-tab')->with('success', __('FsLang::tips.updateSuccess'));
    }

    public function updateSms($itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (empty($config)) {
            return back()->with('failure', $itemKey.' Not Available');
        }

        $verifyCodeTemplate = $config->item_value;

        $verifyCodeTemplate['sms']['status'] = (bool) $request->is_enabled;

        foreach ($request->sign_names as $langTag => $signName) {
            $verifyCodeTemplate['sms']['templates'][$langTag] = [
                'signName' => $signName,
                'templateCode' => $request->template_codes[$langTag],
                'codeParam' => $request->code_params[$langTag],
            ];
        }

        $config->item_value = $verifyCodeTemplate;
        $config->save();

        return redirect(route('panel.send.index').'#templates-tab')->with('success', __('FsLang::tips.updateSuccess'));
    }
}

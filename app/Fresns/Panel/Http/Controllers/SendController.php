<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
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
        ];

        $templateConfigKeys = [
            __('FsLang::panel.send_code_template_1') => 'verifycode_template1',
            __('FsLang::panel.send_code_template_2') => 'verifycode_template2',
            __('FsLang::panel.send_code_template_3') => 'verifycode_template3',
            __('FsLang::panel.send_code_template_4') => 'verifycode_template4',
            __('FsLang::panel.send_code_template_5') => 'verifycode_template5',
            __('FsLang::panel.send_code_template_6') => 'verifycode_template6',
            __('FsLang::panel.send_code_template_7') => 'verifycode_template7',
            __('FsLang::panel.send_code_template_8') => 'verifycode_template8',
        ];

        $codeConfigs = Config::whereIn('item_key', $templateConfigKeys)->get();
        foreach ($codeConfigs as $config) {
            $originValue = collect($config->item_value);
            $value['email'] = $originValue->where('type', 'email')->first();
            $value['sms'] = $originValue->where('type', 'sms')->first();
            $codeParams[$config->item_key] = $value;
        }

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $params['send_sms_supported_codes'] = join(PHP_EOL, $params['send_sms_supported_codes']);

        $pluginScenes = [
            'sendEmail',
            'sendSms',
        ];

        $plugins = Plugin::all();

        $pluginParams = [];
        foreach ($pluginScenes as $scene) {
            $pluginParams[$scene] = $plugins->filter(function ($plugin) use ($scene) {
                return in_array($scene, $plugin->scene);
            });
        }

        return view('FsView::systems.send', compact('params', 'pluginParams', 'templateConfigKeys', 'codeParams'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'send_email_service',
            'send_sms_service',
            'send_sms_default_code',
            'send_sms_supported_codes',
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
        $config = Config::where('item_key', $itemKey)->firstOrFail();

        $emailTemplates = [];
        foreach ($request->titles as $langTag => $title) {
            $emailTemplates[] = [
                'langTag' => $langTag,
                'title' => $title,
                'content' => $request->contents[$langTag],
            ];
        }

        $value = $config->item_value;
        foreach ($value as &$item) {
            if ($item['type'] == 'email') {
                $item['isEnabled'] = $request->is_enabled ? true : false;
                $item['template'] = $emailTemplates;
            }
        }

        $config->item_value = $value;
        $config->save();

        return redirect(route('panel.send.index').'#templates-tab')->with('success', __('FsLang::tips.updateSuccess'));
    }

    public function updateSms($itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->firstOrFail();

        $smsTemplates = [];
        foreach ($request->sign_names as $langTag => $signName) {
            $smsTemplates[] = [
                'langTag' => $langTag,
                'signName' => $signName,
                'templateCode' => $request->template_codes[$langTag],
                'codeParam' => $request->code_params[$langTag],
            ];
        }

        $value = $config->item_value;
        foreach ($value as &$item) {
            if ($item['type'] == 'sms') {
                $item['isEnabled'] = $request->is_enabled ? true : false;
                $item['template'] = $smsTemplates;
            }
        }

        $config->item_value = $value;
        $config->save();

        return redirect(route('panel.send.index').'#templates-tab')->with('success', __('FsLang::tips.updateSuccess'));
    }
}

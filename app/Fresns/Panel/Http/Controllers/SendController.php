<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
}

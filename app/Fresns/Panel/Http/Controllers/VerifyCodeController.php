<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class VerifyCodeController extends Controller
{
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
                $item['isEnable'] = $request->is_enable ? true : false;
                $item['template'] = $emailTemplates;
            }
        }

        $config->item_value = $value;
        $config->save();

        return $this->updateSuccess();
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
                $item['isEnable'] = $request->is_enable ? true : false;
                $item['template'] = $smsTemplates;
            }
        }

        $config->item_value = $value;
        $config->save();

        return $this->updateSuccess();
    }
}

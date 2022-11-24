<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Http\Request;

class CodeMessageController extends Controller
{
    public function index(Request $request)
    {
        $configKeys = [
            'language_status',
            'default_language',
            'language_menus',
        ];

        $langConfigs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($langConfigs as $config) {
            $languageConfig[$config->item_key] = $config->item_value;
        }

        $unikeyArr = CodeMessage::pluck('plugin_unikey')->toArray();
        $unikeys = array_unique($unikeyArr);
        $pluginList = Plugin::whereIn('unikey', $unikeys)->get(['unikey', 'name'])->toArray();

        $langTag = $request->lang_tag ?: $languageConfig['default_language'];

        $codeMessages = CodeMessage::where('lang_tag', $langTag);

        if ($pluginUnikey = $request->plugin_unikey) {
            $codeMessages->where('plugin_unikey', $pluginUnikey);
        }

        if ($code = $request->code) {
            $codeMessages->where('code', $code);
        }
        $codeMessages = $codeMessages->paginate(20);
        $brotherCodeMessages = CodeMessage::whereIn('code', $codeMessages->pluck('code'))->where('lang_tag', '!=', $langTag)->get();

        $codeMessages->map(function ($codeMessage) use ($brotherCodeMessages, $langTag) {
            $messages = $brotherCodeMessages->where('code', $codeMessage->code)->pluck('message', 'lang_tag');
            $messages[$langTag] = $codeMessage->message;
            $codeMessage->messages = $messages;
        });

        return view('FsView::clients.code-messages', compact('languageConfig', 'pluginList', 'codeMessages', 'langTag', 'code', 'pluginUnikey'));
    }

    public function update(CodeMessage $codeMessage, Request $request)
    {
        $codeMessage->message = $request->messages[$codeMessage->lang_tag] ?? '';
        $codeMessage->save();

        foreach ($request->messages as $langTag => $message) {
            if ($langTag == $codeMessage->lang_tag) {
                continue;
            }
            $brotherCodeMessage = CodeMessage::firstOrNew([
                'code' => $codeMessage->code,
                'lang_tag' => $langTag,
                'plugin_unikey' => $codeMessage->plugin_unikey,
            ]);

            $brotherCodeMessage->message = $message ?: '';
            $brotherCodeMessage->save();
        }

        return $this->updateSuccess();
    }
}

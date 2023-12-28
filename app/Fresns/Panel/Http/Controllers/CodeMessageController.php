<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use App\Models\CodeMessage;
use Illuminate\Http\Request;

class CodeMessageController extends Controller
{
    public function index(Request $request)
    {
        $fskeyArr = CodeMessage::pluck('app_fskey')->toArray();
        $fskeys = array_unique($fskeyArr);
        $appList = App::whereIn('fskey', $fskeys)->get(['fskey', 'name'])->toArray();

        $codeMessageQuery = CodeMessage::query();

        $codeMessageQuery->when($request->app_fskey, function ($query, $value) {
            $query->where('app_fskey', $value);
        });

        if (isset($request->code)) {
            $codeMessageQuery->where('code', $request->code);
        }

        $codeMessages = $codeMessageQuery->paginate(50);

        return view('FsView::clients.code-messages', compact('appList', 'codeMessages'));
    }

    public function update(CodeMessage $codeMessage, Request $request)
    {
        if (! $codeMessage) {
            return back()->with('failure', __('FsLang::tips.updateFailure'));
        }

        $messages = $codeMessage->messages;

        foreach ($request->messages as $langTag => $langContent) {
            $messages[$langTag] = $langContent;
        }

        $codeMessage->messages = $messages;
        $codeMessage->save();

        return $this->updateSuccess();
    }

    public function destroy(CodeMessage $codeMessage)
    {
        if (in_array($codeMessage->app_fskey, ['Fresns', 'CmdWord'])) {
            return back()->with('failure', __('FsLang::tips.deleteFailure'));
        }

        $codeMessage->delete();

        return $this->deleteSuccess();
    }
}

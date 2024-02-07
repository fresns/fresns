<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateSessionKeyRequest;
use App\Models\App;
use App\Models\Config;
use App\Models\SessionKey;
use Illuminate\Support\Str;

class AppKeyController extends Controller
{
    public function index()
    {
        $platformConfig = Config::platform()->firstOrFail();
        $platforms = $platformConfig['item_value'];

        $keys = SessionKey::with('app')->get();

        $typeLabels = [
            1 => __('FsLang::panel.key_option_main_api'),
            2 => __('FsLang::panel.key_option_manage_api'),
            3 => __('FsLang::panel.key_option_plugin_api'),
        ];

        $plugins = App::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('apiKey', $plugin->panel_usages);
        });

        return view('FsView::clients.keys', compact('platforms', 'keys', 'typeLabels', 'plugins'));
    }

    public function store(UpdateSessionKeyRequest $request)
    {
        $key = new SessionKey;
        $key->platform_id = $request->platform_id;
        $key->name = $request->name;
        $key->type = $request->type;
        $key->app_fskey = $request->app_fskey;
        $key->app_id = Str::random(8);
        $key->app_key = Str::random(32);
        $key->is_read_only = $request->is_read_only;
        $key->is_enabled = $request->is_enabled;
        $key->save();

        return $this->createSuccess();
    }

    public function update(UpdateSessionKeyRequest $request, SessionKey $key)
    {
        $attributes = $request->all();
        if ($request->type != 3) {
            $attributes['app_fskey'] = null;
        }
        $key->update($attributes);

        return $this->updateSuccess();
    }

    public function reset(SessionKey $key)
    {
        $key->app_key = Str::random(32);
        $key->save();

        return $this->updateSuccess();
    }

    public function destroy(SessionKey $key)
    {
        $key->delete();

        return $this->deleteSuccess();
    }
}

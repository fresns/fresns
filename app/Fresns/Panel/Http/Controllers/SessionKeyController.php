<?php

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
use App\Models\SessionKey;
use App\Fresns\Panel\Http\Requests\UpdateSessionKeyRequest;

class SessionKeyController extends Controller
{
    public function index()
    {
        $platformConfig = Config::platform()->firstOrFail();
        $platforms = $platformConfig['item_value'];

        $keys = SessionKey::with('plugin')->get();

        $typeLabels = [
            1 => __('FsLang::panel.key_option_main_api'),
            2 => __('FsLang::panel.key_option_manage_api'),
            3 => __('FsLang::panel.key_option_plugin_api'),
        ];

        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('apiKey', $plugin->scene ?: []);
        });

        return view('FsView::clients.keys', compact('platforms', 'keys', 'typeLabels', 'plugins'));
    }

    public function store(UpdateSessionKeyRequest $request)
    {
        $key = new SessionKey;
        $key->fill($request->all());
        $key->app_id = \Str::random(8);
        $key->app_secret = \Str::random(32);
        $key->save();

        return $this->createSuccess();
    }

    public function update(UpdateSessionKeyRequest $request, SessionKey $key)
    {
        $attributes = $request->all();
        if ($request->type != 3) {
            $attributes['plugin_unikey'] = null;
        }
        $key->update($attributes);

        return $this->updateSuccess();
    }

    public function reset(SessionKey $key)
    {
        $key->app_secret = \Str::random(32);
        $key->save();

        return $this->updateSuccess();
    }

    public function destroy(SessionKey $key)
    {
        $key->delete();

        return $this->deleteSuccess();
    }
}

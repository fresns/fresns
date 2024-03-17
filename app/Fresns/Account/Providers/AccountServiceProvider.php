<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Providers;

use App\Helpers\ConfigHelper;
use Illuminate\Support\ServiceProvider;

class AccountServiceProvider extends ServiceProvider
{
    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        try {
            $services = ConfigHelper::fresnsConfigByItemKeys([
                'account_center_service',
                'account_register_service',
                'account_login_service',
            ]);

            if ($services['account_center_service'] && $services['account_register_service'] && $services['account_login_service']) {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $this->app->register(RouteServiceProvider::class);

        $this->registerViews();
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $this->loadViewsFrom(dirname(__DIR__, 1).'/Resources/views', 'FsAccountView');
    }
}

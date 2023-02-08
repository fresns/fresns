<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Providers;

use App\Fresns\Panel\Listeners\ExtensionInstalledListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // plugin
        'plugin:installing' => [
            //
        ],

        'plugin:installed' => [
            // get plugin.json insert into database
            ExtensionInstalledListener::class,
        ],

        'plugin:activating' => [
            //
        ],

        'plugin:activated' => [
            // activate plugin
        ],

        'plugin:deactivating' => [
            //
        ],

        'plugin:deactivated' => [
            // deactivate plugin
        ],

        'plugin:uninstalling' => [
            //
        ],

        'plugin:uninstalled' => [
            // delete database data
            ExtensionInstalledListener::class,
        ],

        // theme
        'theme:installing' => [
            //
        ],

        'theme:installed' => [
            // get theme.json insert into database
            ExtensionInstalledListener::class,
        ],

        'theme:uninstalling' => [
            //
        ],

        'theme:uninstalled' => [
            // delete database data
            ExtensionInstalledListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}

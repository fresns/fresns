<?php

return [
    // Common Service Providers
    App\Providers\AppServiceProvider::class,
    App\Providers\SqlLogServiceProvider::class,

    // Fresns Service Providers
    App\Fresns\Install\Providers\InstallServiceProvider::class,
    App\Fresns\Panel\Providers\PanelServiceProvider::class,
    App\Fresns\Words\Providers\CmdWordServiceProvider::class,
    App\Fresns\Account\Providers\AccountServiceProvider::class,
    App\Fresns\Api\Providers\ApiServiceProvider::class,
];

<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe\Providers;

use App\Fresns\Subscribe\SubscribeService;
use Fresns\CmdWordManager\Contracts\CmdWordProviderContract;
use Fresns\CmdWordManager\Traits\CmdWordProviderTrait;
use Illuminate\Support\ServiceProvider;

class CmdWordServiceProvider extends ServiceProvider implements CmdWordProviderContract
{
    use CmdWordProviderTrait;

    protected $fsKeyName = 'Fresns';

    /**
     * @var array[]
     */
    protected $cmdWordsMap = [
        ['word' => 'addSubscribeItem', 'provider' => [SubscribeService::class, 'addSubscribeItem']],
        ['word' => 'deleteSubscribeItem', 'provider' => [SubscribeService::class, 'deleteSubscribeItem']],
        ['word' => 'notifyDataChange', 'provider' => [SubscribeService::class, 'notifyDataChange']],
        ['word' => 'notifyUserActivate', 'provider' => [SubscribeService::class, 'notifyUserActivate']],
    ];

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->registerCmdWordProvider();
    }
}

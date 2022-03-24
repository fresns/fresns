<?php

namespace App\Fresns\Subscribe\Providers;

use Illuminate\Support\ServiceProvider;
use App\Fresns\Subscribe\SubscribeService;

class CmdWordServiceProvider extends ServiceProvider implements \Fresns\CmdWordManager\Contracts\CmdWordProviderContract
{
    use \Fresns\CmdWordManager\Traits\CmdWordProviderTrait;

    protected $unikeyName = 'Fresns';

    /**
     *
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
     *
     * @return void
     */
    public function register()
    {
        $this->registerCmdWordProvider();
    }
}

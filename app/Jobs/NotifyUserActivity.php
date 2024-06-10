<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Jobs;

use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Utilities\SubscribeUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyUserActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $wordBody;

    public function __construct(array $wordBody)
    {
        $this->wordBody = $wordBody;
    }

    public function handle(): void
    {
        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_USER_ACTIVITY);
        if (empty($subscribeItems)) {
            return;
        }

        $wordBody = $this->wordBody;

        $uid = $wordBody['headers']['x-fresns-uid'];

        $cacheKey = "fresns_user_activity_{$uid}";
        $cacheTag = 'fresnsUsers';

        $userCache = CacheHelper::get($cacheKey, $cacheTag);
        if (empty($userCache)) {
            $user = PrimaryHelper::fresnsModelByFsid('user', $uid);

            $user?->update([
                'last_activity_at' => now(),
            ]);

            CacheHelper::put(now(), $cacheKey, $cacheTag, 10, 10);
        }

        foreach ($subscribeItems as $item) {
            $fskey = $item['fskey'];
            $cmdWord = $item['cmdWord'];

            ProcessCommand::dispatch($fskey, $cmdWord, $wordBody);
        }
    }
}

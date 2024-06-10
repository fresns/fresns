<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Jobs;

use App\Utilities\SubscribeUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyAccountAndUserLogin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $wordBody;

    public function __construct(array $wordBody)
    {
        $this->wordBody = $wordBody;
    }

    public function handle(): void
    {
        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_ACCOUNT_AND_USER_LOGIN);
        if (empty($subscribeItems)) {
            return;
        }

        foreach ($subscribeItems as $item) {
            $fskey = $item['fskey'];
            $cmdWord = $item['cmdWord'];

            ProcessCommand::dispatch($fskey, $cmdWord, $this->wordBody);
        }
    }
}

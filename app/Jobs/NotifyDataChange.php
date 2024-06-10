<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Jobs;

use App\Helpers\StrHelper;
use App\Utilities\SubscribeUtility;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyDataChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tableName;
    protected $primaryId;
    protected $changeType;

    public function __construct(mixed $tableName, int $primaryId, string $changeType)
    {
        $this->tableName = $tableName;
        $this->primaryId = $primaryId;
        $this->changeType = $changeType;
    }

    public function handle(): void
    {
        $subTableName = null;
        try {
            if ($this->tableName) {
                $subTableName = StrHelper::qualifyTableName($this->tableName);
            }
        } catch (\Exception $e) {
            return;
        }

        if (empty($subTableName)) {
            return;
        }

        $subscribeItems = SubscribeUtility::getSubscribeItems(SubscribeUtility::TYPE_TABLE_DATA_CHANGE, $subTableName);
        if (empty($subscribeItems)) {
            return;
        }

        $wordBody = [
            'tableName' => $subTableName,
            'primaryId' => $this->primaryId,
            'changeType' => $this->changeType,
        ];

        foreach ($subscribeItems as $item) {
            $fskey = $item['fskey'];
            $cmdWord = $item['cmdWord'];

            ProcessCommand::dispatch($fskey, $cmdWord, $wordBody);
        }
    }
}

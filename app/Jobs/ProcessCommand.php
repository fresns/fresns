<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCommand implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $fskey;
    protected $cmdWord;
    protected $wordBody;

    public function __construct(string $fskey, string $cmdWord, mixed $wordBody)
    {
        $this->fskey = $fskey;
        $this->cmdWord = $cmdWord;
        $this->wordBody = $wordBody;
    }

    public function handle()
    {
        $fskey = $this->fskey;
        $cmdWord = $this->cmdWord;
        $wordBody = $this->wordBody;

        try {
            \FresnsCmdWord::plugin($fskey)->$cmdWord($wordBody);
        } catch (\Exception $e) {
            info('Error executing cmdWord: '.$e->getMessage(), [$fskey, $cmdWord, $wordBody]);
        }
    }
}

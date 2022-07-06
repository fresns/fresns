<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe;

class UserActivateEvent
{
    protected object $event;

    protected string $uri;
    protected array $header;
    protected array $body;

    public function __construct(object $event)
    {
        $this->event = $event;

        $this->uri = $event->uri;
        $this->header = $event->header;
        $this->body = $event->body;
    }

    public static function make(object $event)
    {
        return new static($event);
    }

    public function notify(Subscribe $subscribe)
    {
        $unikey = $subscribe->getUnikey();
        $cmdWord = $subscribe->getCmdWord();

        \FresnsCmdWord::plugin($unikey)->$cmdWord([
            'uri' => $this->uri,
            'header' => $this->header,
            'body' => $this->body,
        ]);
    }
}

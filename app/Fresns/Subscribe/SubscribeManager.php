<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe;

class SubscribeManager
{
    public function notifyDataChange(object $event)
    {
        \FresnsCmdWord::plugin()->notifyDataChange($event);
    }

    public function handleUserActivateNotify(object $event)
    {
        \FresnsCmdWord::plugin()->notifyUserActivate($event);
    }

    /**
     * @param  \Illuminate\Events\Dispatcher  $events
     * @return void
     */
    public function subscribe($events)
    {
        return [
            'fresns.data.change' => 'notifyDataChange',
            'fresns.user.activate' => 'handleUserActivateNotify',
        ];
    }
}

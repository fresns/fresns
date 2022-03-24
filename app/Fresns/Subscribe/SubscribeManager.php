<?php

namespace App\Fresns\Subscribe;


class SubscribeManager
{
    public function notifyDataChange(object $event)
    {
        \FresnsCmdWord::plugin()->fireSubscribe($event);
    }

    public function handleUserActivateNotify(object $event)
    {
        \FresnsCmdWord::plugin()->notifyUserActivate($event);
    }
    
    /**
     * @param  \Illuminate\Events\Dispatcher $events
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

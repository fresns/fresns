<?php

namespace App\Fresns\Subscribe;

use App\Fresns\Subscribe\Subscribe;
use App\Fresns\Subscribe\TableDataChangeEvent;
use App\Fresns\Subscribe\UserActivateEvent;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;

class SubscribeService
{
    use CmdWordResponseTrait;

    public function addSubscribeItem(array $wordBody)
    {
        $subscribe = Subscribe::make($wordBody);

        // Table does not support subscribe
        if ($subscribe->isNotSupportSubscribe()) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::ERROR_CODE_20005)::throw("unsupported subscription forms {$subscribe->getSubTableName()}");
        }
        
        // Subscribe already exists
        if ($subscribe->ensureSubscribeExists()) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::ERROR_CODE_20005)::throw("unikey {$subscribe->getUnikey()} already subscribed table {$subscribe->getSubTableName()}");
        }

        // Add subscribe
        $subscribe->save();

        return $this->success();
    }

    public function deleteSubscribeItem(array $wordBody)
    {
        $subscribe = Subscribe::make($wordBody);

        if ($subscribe->ensureSubscribeNotExists()) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::ERROR_CODE_20005)::throw("unikey {$subscribe->getUnikey()} unsubscribed table {$subscribe->getSubTableName()}");
        }

        $subscribe->remove();

        return $this->success();
    }

    public function notifyDataChange(object $event)
    {
        $event = TableDataChangeEvent::make($event);
        $subscribe = Subscribe::make();
        
        $subscribe->getTableDataChangeSubscribes()->map(function ($subscribe) use ($event) {
            if ($event->ensureSubscribedByThisTable($subscribe)) {
                $event->notify($subscribe);
            }
        });
    }

    public function notifyUserActivate(object $event)
    {
        $event = UserActivateEvent::make($event);
        $subscribe = Subscribe::make();
        
        $subscribe->getUserActivateSubscribes()->map(function ($subscribe) use ($event) {
            $event->notify($subscribe);
        });
    }
}

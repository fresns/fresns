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

    protected string $platformId;
    protected string $version;
    protected string $appId;
    protected string $langTag;
    protected string $timezone;
    protected string $aid;
    protected string $uid;
    protected string $deviceInfo;
    protected string $uri;
    protected array $body;

    public function __construct(object $event)
    {
        $this->validate((array) $event);

        $this->event = $event;

        $this->platformId = $event->platformId;
        $this->version = $event->version;
        $this->appId = $event->appId;
        $this->langTag = $event->langTag;
        $this->timezone = $event->timezone;
        $this->aid = $event->aid;
        $this->uid = $event->uid;
        $this->deviceInfo = $event->deviceInfo;
        $this->uri = $event->uri;
        $this->body = $event->body;
    }

    public static function make(object $event)
    {
        return new static($event);
    }

    public function validate(array $event)
    {
        \validator()->validate($event, [
            'platformId' => 'required',
            'version' => 'required',
            'appId' => 'required',
            'langTag' => 'nullable',
            'timezone' => 'nullable',
            'aid' => 'required',
            'uid' => 'nullable',
            'deviceInfo' => 'required',
            'uri' => 'required|string',
            'body' => 'required|array',
        ]);
    }

    public function notify(Subscribe $subscribe)
    {
        $unikey = $subscribe->getUnikey();
        $cmdWord = $subscribe->getCmdWord();

        return \FresnsCmdWord::plugin($unikey)->$cmdWord($this->toArray());
    }

    public function toArray()
    {
        return [
            'platformId' => $this->getPlatform(),
            'version' => $this->getVersion(),
            'appId' => $this->getAppId(),
            'langTag' => $this->getLangTag(),
            'timezone' => $this->getTimezone(),
            'aid' => $this->getAid(),
            'uid' => $this->getUid(),
            'deviceInfo' => $this->getDeviceInfo(),
            'uri' => $this->getUri(),
            'body' => $this->getBody(),
        ];
    }

    public function __call(string $methd, array $args)
    {
        $property = str_replace('get', '', $methd);
        $property = lcfirst($property);

        return $this->$property;
    }
}

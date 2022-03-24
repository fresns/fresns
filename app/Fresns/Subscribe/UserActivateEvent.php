<?php

namespace App\Fresns\Subscribe;

class UserActivateEvent
{
    protected object $event;
    protected int $aid;
    protected int $uid;
    protected string $uri;
    protected array $body;
    
    public function __construct(object $event)
    {
        $this->validate((array) $event);

        $this->event = $event;
        
        $this->aid = $event->aid;
        $this->uid = $event->uid;
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
            'aid' => 'required|integer',
            'uid' => 'required|integer',
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
            'aid' => $this->getAid(),
            'uid' => $this->getUid(),
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

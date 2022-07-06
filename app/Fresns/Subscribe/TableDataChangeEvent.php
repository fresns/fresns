<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe;

class TableDataChangeEvent
{
    protected object $event;
    protected string $tableName;
    protected int $primaryId;
    protected string $changeType;

    public function __construct(object $event)
    {
        $this->validate((array) $event);

        $this->event = $event;

        $this->tableName = $event->tableName;
        $this->primaryId = $event->primaryId;
        $this->changeType = $event->changeType;
    }

    public static function make(object $event)
    {
        return new static($event);
    }

    public function validate(array $event)
    {
        \validator()->validate($event, [
            'tableName' => 'required|string',
            'primaryId' => 'required|integer',
            'changeType' => 'required|string',
        ]);
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function getPrimaryId()
    {
        return $this->primaryId;
    }

    public function getChangeType()
    {
        return $this->changeType;
    }

    public function ensureSubscribedByThisTable(Subscribe $subscribe)
    {
        return $this->getTableName() === $subscribe->getSubTableName();
    }

    public function notify(Subscribe $subscribe)
    {
        $unikey = $subscribe->getUnikey();
        $cmdWord = $subscribe->getCmdWord();

        \FresnsCmdWord::plugin($unikey)->$cmdWord($this->toArray());
    }

    public function toArray()
    {
        return [
            'tableName' => $this->getTableName(),
            'primaryId' => $this->getPrimaryId(),
            'changeType' => $this->getChangeType(),
        ];
    }
}

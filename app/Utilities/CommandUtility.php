<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class CommandUtility
{
    protected $executableFinder;

    protected $defaultExtraDirs = [
        '/usr/bin',
        '/usr/local/bin',
    ];

    public function __construct()
    {
        $this->executableFinder = new ExecutableFinder();
    }

    public static function make()
    {
        return new static();
    }

    public function createProcess(array $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60)
    {
        return tap(new Process(...func_get_args()));
    }

    public function findBinary(string $name, array $extraDirs = [])
    {
        return $this->executableFinder->find($name, false, $this->defaultExtraDirs + $extraDirs);
    }

    public static function getPhpProcess(array $argument)
    {
        $instance = new static();

        $php = $instance->findBinary('php');

        return $instance->createProcess([$php, ...$argument]);
    }

    public static function getComposerProcess(array $argument)
    {
        $instance = new static();

        $php = $instance->findBinary('php');

        $composer = $instance->findBinary('composer');

        return $instance->createProcess([$php, $composer, ...$argument]);
    }
}

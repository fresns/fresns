<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use Illuminate\Support\Str;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class CommandUtility
{
    protected $executableFinder;

    protected $defaultExtraDirs = [
        '/usr/bin/',
        '/usr/local/bin/',
    ];

    public function __construct()
    {
        $this->executableFinder = new ExecutableFinder();

        if (function_exists('base_path')) {
            $this->defaultExtraDirs = array_merge($this->defaultExtraDirs, [base_path()]);
        }
    }

    public static function make(): static
    {
        return new static();
    }

    public static function getRealpath($path): static|bool
    {
        return realpath($path);
    }

    public static function formatCommand($command): mixed
    {
        if (is_string($command)) {
            $command = explode(' ', $command);
        }

        return $command;
    }

    public function createProcess(array $command, string $cwd = null, array $env = null, $input = null, ?float $timeout = 60): mixed
    {
        return tap(new Process(...func_get_args()));
    }

    public static function findBinary(string $name, array $extraDirs = []): ?string
    {
        $instance = static::make();

        $extraDirs = array_merge($instance->defaultExtraDirs, $extraDirs);

        $extraDirs = array_map(fn ($dir) => rtrim($dir, '/'), $extraDirs);

        $path = $instance->executableFinder->find($name, null, $extraDirs);

        if (empty($path)) {
            $disableFunctions = explode(',', ini_get('disable_functions'));

            if (function_exists('shell_exec') && ! in_array('shell_exec', $disableFunctions)) {
                switch ($name) {
                    case 'php':
                        $path = shell_exec('which php');
                        break;

                    case 'composer':
                        $path = shell_exec('which composer');
                        break;

                    default:
                        return $path;
                }

                return trim($path);
            }

            switch ($name) {
                case 'php':
                    return '/usr/bin/php';
                    break;

                case 'composer':
                    return '/usr/bin/composer';
                    break;

                default:
                    return $path;
            }
        }

        return $path;
    }

    public static function getPhpProcess(array $argument): mixed
    {
        $instance = new static();

        $php = $instance->findBinary('php');

        return $instance->createProcess([$php, ...$argument]);
    }

    public static function getComposerProcess(array $argument): mixed
    {
        $instance = new static();

        $composer = $instance->findBinary('composer');

        if (Str::endsWith($composer, '.phar')) {
            $php = $instance->findBinary('php');

            return $instance->createProcess([$php, $composer, ...$argument]);
        }

        return $instance->createProcess([$composer, ...$argument]);
    }
}

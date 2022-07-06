<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Auth;

interface Guard
{
    public function authenticate(): array;

    public function has(): bool;

    public function check(): bool;

    public function guest(): bool;

    public function aid(): string;

    public function set(array $params): self;

    public function get(?string $key = null);

    public function logout();
}

<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Client;

use Illuminate\Support\Arr;

trait Arrayable
{
    protected array $attributes = [];

    public static function makeAttribute(array $attributes = [])
    {
        $instance = new class implements \ArrayAccess
        {
            use Arrayable;
        };

        $instance->setAttributes($attributes);

        return $instance;
    }

    public function setAttributes(array $attributes = [])
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function offsetExists(mixed $offset): bool
    {
        return Arr::has($this->attributes, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $value = Arr::get($this->attributes, $offset);
        if (is_array($value)) {
            return static::makeAttribute($value);
        }

        return $value ?? null;
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        Arr::set($this->attributes, $key, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        Arr::forget($this->attributes, $offset);
    }

    public function __set(string $attribute, mixed $value): void
    {
        $this[$attribute] = $value;
    }

    public function __get(string $attribute): mixed
    {
        return $this[$attribute];
    }

    public function toArray()
    {
        return $this->getAttributes();
    }
}

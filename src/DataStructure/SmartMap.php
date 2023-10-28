<?php

namespace Awwar\PhpHttpEntityManager\DataStructure;

use ArrayAccess;

class SmartMap implements ArrayAccess
{
    public function __construct(private array $map = [])
    {
    }

    public static function create(array $map = []): self
    {
        return new SmartMap($map);
    }

    public function put(?string $path = null, mixed $value = []): void
    {
        if ($path === null) {
            return;
        }

        $pathList = explode('.', $path);
        $buffer = $this->map;
        $current =& $buffer;

        foreach ($pathList as $i => $segment) {
            $current[$segment] = $buffer[$segment] ?? [];
            $current =& $current[$segment];
        }

        $current = $value;

        $this->map = $buffer;
    }

    public function get(?string $path = null, mixed $default = null): mixed
    {
        if ($path === '') {
            return $this->map;
        }

        if (isset($this->map[$path])) {
            return $this->map[$path];
        }

        if ($path === null || !str_contains($path, '.')) {
            return $default;
        }

        $buffer = $this->map;
        $pathList = explode('.', $path);

        foreach ($pathList as $segment) {
            if (!is_array($buffer) || false === isset($buffer[$segment])) {
                return $default;
            }
            $buffer =& $buffer[$segment];
        }

        return $buffer;
    }

    public function toArray(): array
    {
        return $this->map;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->map[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->map[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->map[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->map[$offset]);
    }
}

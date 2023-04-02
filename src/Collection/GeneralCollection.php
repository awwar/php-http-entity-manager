<?php

namespace Awwar\PhpHttpEntityManager\Collection;

use Closure;
use Traversable;

class GeneralCollection implements CollectionInterface
{
    public function __construct(protected array $collection = [])
    {
    }

    public function count(): int
    {
        return count($this->collection);
    }

    public function getIterator(): Traversable
    {
        foreach ($this->collection as $item) {
            yield $item;
        }
    }

    public function offsetExists($offset): bool
    {
        return isset($this->collection[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->collection[$offset]);
    }

    public function map(Closure $callback): array
    {
        $result = [];

        foreach ($this->collection as $key => $value) {
            $result [] = call_user_func($callback, $key, $value);
        }
        return $result;
    }

    public function reduce(Closure $callback, mixed $accumulator): mixed
    {
        foreach ($this->collection as $key => $value) {
            $accumulator = call_user_func($callback, $key, $value, $accumulator);
        }

        return $accumulator;
    }

    public function filter(Closure $callback): CollectionInterface
    {
        $self = new self();

        foreach ($this->collection as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $self->offsetSet($key, $value);
            }
        }

        return $self;
    }

    public function each(Closure $callback): void
    {
        foreach ($this->collection as $key => $value) {
            call_user_func($callback, $key, $value);
        }
    }

    public function first(): mixed
    {
        return $this->collection[array_key_first($this->collection)] ?? null;
    }

    public function last(): mixed
    {
        return $this->collection[array_key_last($this->collection)] ?? null;
    }

    public function find(Closure $callback, mixed $default = null): mixed
    {
        foreach ($this->collection as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                return $value;
            }
        }

        return $default;
    }

    public function isEmpty(): bool
    {
        return count($this->collection) === 0;
    }

    public function countOf(Closure $callback): int
    {
        $count = 0;

        foreach ($this->collection as $key => $value) {
            if (call_user_func($callback, $key, $value)) {
                $count++;
            }
        }

        return $count;
    }
}

<?php

namespace Awwar\PhpHttpEntityManager\Http\Collection;

use Traversable;

class GeneralCollection implements Collection
{
    public function __construct(private array $collection = [])
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
}

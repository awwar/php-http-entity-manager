<?php

namespace Awwar\PhpHttpEntityManager\Proxy;

use Awwar\PhpHttpEntityManager\Collection\Collection;
use Closure;
use Traversable;

class ProxyCollection implements Collection
{
    private array $collection = [];

    private bool $isInitialized = false;

    public function __construct(private Closure $initiator)
    {
    }

    public function count(): int
    {
        $this->tryInit();

        return count($this->collection);
    }

    public function getIterator(): Traversable
    {
        $this->tryInit();
        foreach ($this->collection as $item) {
            yield $item;
        }
    }

    public function offsetExists($offset): bool
    {
        $this->tryInit();

        return isset($this->collection[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        $this->tryInit();

        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->tryInit();
        $this->collection[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->tryInit();
        unset($this->collection[$offset]);
    }

    private function tryInit(): void
    {
        if ($this->isInitialized === false) {
            $iterator = $this->initiator->call($this);

            foreach ($iterator as $item) {
                $this->collection[] = $item;
            }

            $this->isInitialized = true;
        }
    }
}

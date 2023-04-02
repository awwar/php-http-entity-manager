<?php

namespace Awwar\PhpHttpEntityManager\Proxy;

use Awwar\PhpHttpEntityManager\Collection\GeneralCollection;
use Closure;
use Traversable;

class ProxyCollection extends GeneralCollection
{
    private bool $isInitialized = false;

    public function __construct(private Closure $initiator, array $collection = [])
    {
        parent::__construct($collection);
    }

    public function count(): int
    {
        $this->tryInit();

        return parent::count();
    }

    public function getIterator(): Traversable
    {
        $this->tryInit();

        return parent::getIterator();
    }

    public function offsetExists($offset): bool
    {
        $this->tryInit();

        return parent::offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        $this->tryInit();

        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->tryInit();

        parent::offsetSet($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->tryInit();

        parent::offsetUnset($offset);
    }

    private function tryInit(): void
    {
        if ($this->isInitialized === false) {
            $iterator = $this->initiator->call($this);

            foreach ($iterator as $i => $item) {
                $this->collection[$i] = $item;
            }

            $this->isInitialized = true;
        }
    }
}

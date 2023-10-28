<?php

namespace Awwar\PhpHttpEntityManager\Collection;

use ArrayAccess;
use Closure;
use Countable;
use IteratorAggregate;

interface CollectionInterface extends Countable, IteratorAggregate, ArrayAccess
{
    public function map(Closure $callback): array;

    public function reduce(Closure $callback, mixed $accumulator): mixed;

    public function filter(Closure $callback): CollectionInterface;

    public function each(Closure $callback): void;

    public function first(): mixed;

    public function last(): mixed;

    public function find(Closure $callback, mixed $default = null): mixed;

    public function isEmpty(): bool;

    public function countOf(Closure $callback): int;

    public function contains(mixed $item): bool;
}

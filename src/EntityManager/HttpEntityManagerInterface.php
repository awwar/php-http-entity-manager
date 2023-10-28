<?php

namespace Awwar\PhpHttpEntityManager\EntityManager;

use Awwar\PhpHttpEntityManager\Repository\HttpRepositoryInterface;
use Generator;

interface HttpEntityManagerInterface
{
    public function clear(string $objectName = null): void;

    public function contains(object $object): bool;

    public function detach(object $object): void;

    public function find(string $className, mixed $id, array $criteria = []): object;

    public function flush(): void;

    public function getRepository(string $className): HttpRepositoryInterface;

    public function iterate(
        string $className,
        array $criteria,
        ?string $url = null,
        bool $isFilterOne = false
    ): Generator;

    public function persist(object $object): void;

    public function refresh(object $object): void;

    public function remove(object $object): void;
}

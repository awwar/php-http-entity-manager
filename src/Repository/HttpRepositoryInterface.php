<?php

namespace Awwar\PhpHttpEntityManager\Repository;

use Generator;

interface HttpRepositoryInterface
{
    public function add(object $object, bool $flush = false): void;

    public function filter(array $filter = []): Generator;

    public function filterOne(array $filter = []): object;

    public function find(mixed $id, array $criteria = []): object;

    public function remove(object $object, bool $flush = false): void;
}

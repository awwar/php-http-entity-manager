<?php

namespace Awwar\PhpHttpEntityManager\Repository;

use Awwar\PhpHttpEntityManager\EntityManager\HttpEntityManagerInterface;
use Generator;

class HttpRepository implements HttpRepositoryInterface
{
    public function __construct(
        private HttpEntityManagerInterface $httpEntityManager,
        private string $entityClass
    ) {
    }

    public function add(object $object, bool $flush = false): void
    {
        $this->httpEntityManager->persist($object);

        if ($flush) {
            $this->httpEntityManager->flush();
        }
    }

    public function filter(array $filter = []): Generator
    {
        return $this->httpEntityManager->iterate($this->entityClass, $filter);
    }

    public function filterOne(array $filter = []): object
    {
        return $this->httpEntityManager->iterate($this->entityClass, $filter, isFilterOne: true)->current();
    }

    public function find(mixed $id, array $criteria = []): object
    {
        return $this->httpEntityManager->find($this->entityClass, $id, $criteria);
    }

    public function remove(object $object, bool $flush = false): void
    {
        $this->httpEntityManager->remove($object);

        if ($flush) {
            $this->httpEntityManager->flush();
        }
    }
}

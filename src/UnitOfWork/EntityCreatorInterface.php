<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

interface EntityCreatorInterface
{
    public function createEntityWithData(string $className, mixed $data): ?object;
}

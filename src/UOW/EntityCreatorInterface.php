<?php

namespace Awwar\PhpHttpEntityManager\UOW;

interface EntityCreatorInterface
{
    public function createEntityWithData(string $className, mixed $data): ?object;
}

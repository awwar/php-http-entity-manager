<?php

namespace Awwar\PhpHttpEntityManager\Http;

use Awwar\PhpHttpEntityManager\UOW\RelationMapping;

interface EntityCreatorInterface
{
    public function createEntityWithData(string $className, mixed $data): ?object;
}

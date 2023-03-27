<?php

namespace Awwar\PhpHttpEntityManager\Http;

interface EntityCreatorInterface
{
    public function createEntityWithData(string $className, mixed $data): ?object;
}

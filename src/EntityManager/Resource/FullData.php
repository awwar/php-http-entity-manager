<?php

namespace Awwar\PhpHttpEntityManager\EntityManager\Resource;

class FullData
{
    public function __construct(private array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}

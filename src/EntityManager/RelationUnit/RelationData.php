<?php

namespace Awwar\PhpHttpEntityManager\EntityManager\RelationUnit;

class RelationData
{
    public function __construct(private array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }
}

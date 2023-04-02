<?php

namespace Awwar\PhpHttpEntityManager\EntityManager\RelationUnit;

class Reference
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}

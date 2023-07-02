<?php

namespace Awwar\PhpHttpEntityManager\EntityManager\RelationUnit;

class RelationReference
{
    public function __construct(private string $id)
    {
    }

    public function getId(): string
    {
        return $this->id;
    }
}

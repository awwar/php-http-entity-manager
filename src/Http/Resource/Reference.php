<?php

namespace Awwar\PhpHttpEntityManager\Http\Resource;

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

<?php

namespace Awwar\PhpHttpEntityManager\Http\Resource;

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

<?php

namespace Awwar\PhpHttpEntityManager\EntityManager\ListUnit;

class Item
{
    public function __construct(private array $data, private ?string $nextPageUrl)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getNextPageUrl(): ?string
    {
        return $this->nextPageUrl;
    }
}

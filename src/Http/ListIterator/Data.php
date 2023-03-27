<?php

namespace Awwar\PhpHttpEntityManager\Http\ListIterator;

class Data
{
    public function __construct(private array $data, private ?string $url)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }
}

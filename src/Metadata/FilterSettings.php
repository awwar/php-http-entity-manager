<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

class FilterSettings
{
    public function __construct(
        private array $filterQuery = [],
        private array $getOneQuery = [],
        private array $filterOneQuery = [],
    ) {
    }

    public function getFilterOneQuery(): array
    {
        return $this->filterOneQuery;
    }

    public function getFilterQuery(): array
    {
        return $this->filterQuery;
    }

    public function getGetOneQuery(): array
    {
        return $this->getOneQuery;
    }
}

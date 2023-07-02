<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

class FilterSettings
{
    public function __construct(
        private array $onFilterQueryMixin = [],
        private array $onGetOneQueryMixin = [],
        private array $onFindOneQueryMixin = [],
    ) {
    }

    public function getOnFindOneQueryMixin(): array
    {
        return $this->onFindOneQueryMixin;
    }

    public function getOnFilterQueryMixin(): array
    {
        return $this->onFilterQueryMixin;
    }

    public function getOnGetOneQueryMixin(): array
    {
        return $this->onGetOneQueryMixin;
    }
}

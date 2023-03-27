<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

class UrlSettings
{
    public function __construct(
        private string $one,
        private string $list,
        private string $create,
        private string $update,
        private string $delete,
    ) {
    }

    public function getCreate(): string
    {
        return $this->create;
    }

    public function getDelete(): string
    {
        return $this->delete;
    }

    public function getList(): string
    {
        return $this->list;
    }

    public function getOne(): string
    {
        return $this->one;
    }

    public function getUpdate(): string
    {
        return $this->update;
    }
}

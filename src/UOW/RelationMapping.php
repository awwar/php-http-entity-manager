<?php

namespace Awwar\PhpHttpEntityManager\UOW;

use Awwar\PhpHttpEntityManager\Annotation\RelationField;

class RelationMapping
{
    private string $class;

    private string $name;

    private bool $isCollection;

    public static function create(array $data): self
    {
        $mapping = new self();

        $mapping->class = $data['class'];
        $mapping->name = $data['name'];
        $mapping->isCollection = $data['expects'] === RelationField::MANY;
        //$mapping->lateUrl = $data['lateUrl'];

        return $mapping;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function getDefault(): ?array
    {
        return $this->isCollection ? [] : null;
    }
}

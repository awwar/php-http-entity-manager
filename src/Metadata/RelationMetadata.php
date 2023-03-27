<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Awwar\PhpHttpEntityManager\Enum\RelationExpectsEnum;

class RelationMetadata
{
    private string $class;

    private string $name;

    private bool $isCollection;

    public static function create(array $data): self
    {
        $mapping = new self();

        $mapping->class = $data['class'];
        $mapping->name = $data['name'];
        $mapping->isCollection = $data['expects'] === RelationExpectsEnum::MANY;

        //$mapping->lateUrl = $data['lateUrl'];

        return $mapping;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getDefault(): ?array
    {
        return $this->isCollection ? [] : null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }
}

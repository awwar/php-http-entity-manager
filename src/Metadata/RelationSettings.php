<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Awwar\PhpHttpEntityManager\Enum\RelationExpectsEnum;
use InvalidArgumentException;

class RelationSettings
{
    private bool $isCollection;

    public function __construct(
        private string $class,
        private string $name,
        int $expects
    ) {
        if (in_array($expects, RelationExpectsEnum::ALL) === false) {
            throw new InvalidArgumentException('Relation expectation must be only "many", "one"');
        }

        $this->isCollection = $expects === RelationExpectsEnum::MANY;
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

<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use InvalidArgumentException;

class RelationSettings
{
    public const ALL = [
        self::ONE,
        self::MANY,
    ];

    public const ONE = 0;

    public const MANY = 1;

    private bool $isCollection;

    public function __construct(
        private string $class,
        private string $name,
        int $expects
    ) {
        if (in_array($expects, self::ALL) === false) {
            throw new InvalidArgumentException('Relation expectation must be only "many", "one"');
        }

        $this->isCollection = $expects === self::MANY;
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

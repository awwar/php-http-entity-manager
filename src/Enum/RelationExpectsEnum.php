<?php

namespace Awwar\PhpHttpEntityManager\Enum;

interface RelationExpectsEnum
{
    public const ALL = [
        self::ONE,
        self::MANY,
    ];

    public const ONE = 0;

    public const MANY = 1;
}

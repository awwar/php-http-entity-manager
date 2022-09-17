<?php

namespace Awwar\PhpHttpEntityManager\Annotation;

interface CacheableAnnotation
{
    public function toArray(): array;

    public static function getDefault(): array;
}

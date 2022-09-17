<?php

namespace Awwar\PhpHttpEntityManager\UOW;

interface MetadataRegistryInterface
{
    public function get(string $className): EntityMetadata;
}

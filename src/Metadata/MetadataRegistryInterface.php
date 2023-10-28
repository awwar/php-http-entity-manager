<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

interface MetadataRegistryInterface
{
    public function get(string $className): EntityMetadata;
}

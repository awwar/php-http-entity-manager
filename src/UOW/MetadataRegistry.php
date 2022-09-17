<?php

namespace Awwar\PhpHttpEntityManager\UOW;

use Exception;
use RuntimeException;

class MetadataRegistry implements MetadataRegistryInterface
{
    private array $metadataMap = [];
    private array $proxyAliases = [];

    /**
     * @throws Exception
     */
    public function __construct(array $metadataMap = [])
    {
        foreach ($metadataMap as $data) {
            $metadata = new EntityMetadata($data);

            $proxyClass = $metadata->getProxyClass();
            $originalClass = $metadata->getClassName();

            $this->proxyAliases[$proxyClass] = $originalClass;
            $this->metadataMap[$originalClass] = $metadata;
        }
    }

    /**
     * @throws Exception
     */
    public function get(string $className): EntityMetadata
    {
        if (isset($this->proxyAliases[$className])) {
            $className = $this->proxyAliases[$className];
        }

        if (false === isset($this->metadataMap[$className])) {
            throw new RuntimeException("Entity is wrong configured: unable to find metadata for $className");
        }

        return $this->metadataMap[$className];
    }
}

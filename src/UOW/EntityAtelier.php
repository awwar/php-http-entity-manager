<?php

namespace Awwar\PhpHttpEntityManager\UOW;

use Awwar\PhpHttpEntityManager\Metadata\MetadataRegistryInterface;

class EntityAtelier
{
    public function __construct(private MetadataRegistryInterface $metadataRegistry)
    {
    }

    public function suitUpClass(string $entityClass): SuitedUpEntity
    {
        $metadata = $this->metadataRegistry->get($entityClass);

        return SuitedUpEntity::create($metadata->getEmptyInstance(), $metadata);
    }

    public function suitUpEntity(object $entity): SuitedUpEntity
    {
        $metadata = $this->metadataRegistry->get(get_class($entity));

        return SuitedUpEntity::create($entity, $metadata);
    }
}

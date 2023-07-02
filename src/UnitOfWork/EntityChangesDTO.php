<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

class EntityChangesDTO
{
    public function __construct(
        private array $entityChanges,
        private array $relationChanges,
        private array $entityData,
        private array $relationData
    ) {
    }

    public function getEntityChanges(): array
    {
        return $this->entityChanges;
    }

    public function getRelationChanges(): array
    {
        return $this->relationChanges;
    }

    public function getEntityData(): array
    {
        return $this->entityData;
    }

    public function getRelationData(): array
    {
        return $this->relationData;
    }
}

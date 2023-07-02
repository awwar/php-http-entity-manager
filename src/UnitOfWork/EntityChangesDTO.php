<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

class EntityChangesDTO
{
    public function __construct(
        private array $entityChanges,
        private array $relationChanges,
        private array $entitySnapshot,
        private array $relationsSnapshot
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

    public function getEntitySnapshot(): array
    {
        return $this->entitySnapshot;
    }

    public function getRelationsSnapshot(): array
    {
        return $this->relationsSnapshot;
    }
}

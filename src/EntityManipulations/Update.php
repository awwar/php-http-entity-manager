<?php

namespace Awwar\PhpHttpEntityManager\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

class Update implements ManipulationCommandInterface
{
    public function __construct(
        private SuitedUpEntity $suit,
        private array $entityChanges = [],
        private array $relationChanges = [],
    ) {
    }

    public function execute(): void
    {
        $data = $this->suit->callBeforeUpdate(
            $this->entityChanges,
            $this->relationChanges,
            $this->suit->getScalarSnapshot(),
            $this->suit->getRelationValues(),
        );
        $metadata = $this->suit->getMetadata();

        $result = $metadata->getClient()->update($metadata->getUrlForUpdate($this->suit->getId()), $data);

        $this->suit->callAfterUpdate($result);
    }

    public function getSuit(): SuitedUpEntity
    {
        return $this->suit;
    }
}

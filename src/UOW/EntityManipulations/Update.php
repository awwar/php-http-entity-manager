<?php

namespace Awwar\PhpHttpEntityManager\UOW\EntityManipulations;

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
        $metadata = $this->suit->getMetadata();
        $url = $metadata->getUrlForUpdate($this->suit->getId());
        $data = $this->suit->callBeforeUpdate($this->entityChanges, $this->relationChanges);

        $result = $metadata->getClient()->update($url, $data);

        $this->suit->callAfterUpdate($result);
    }

    public function getSuit(): SuitedUpEntity
    {
        return $this->suit;
    }
}

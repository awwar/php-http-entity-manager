<?php

namespace Awwar\PhpHttpEntityManager\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

class Delete implements ManipulationCommandInterface
{
    public function __construct(private SuitedUpEntity $suit)
    {
    }

    public function execute(): void
    {
        $metadata = $this->suit->getMetadata();
        $metadata->getClient()->delete($metadata->getUrlForDelete($this->suit->getId()));
    }

    public function getSuit(): SuitedUpEntity
    {
        return $this->suit;
    }
}

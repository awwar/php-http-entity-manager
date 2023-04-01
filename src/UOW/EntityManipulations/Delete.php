<?php

namespace Awwar\PhpHttpEntityManager\UOW\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

class Delete implements ManipulationCommandInterface
{
    public function __construct(private SuitedUpEntity $suit)
    {
    }

    public function execute(): void
    {
        $metadata = $this->suit->getMetadata();
        $url = $metadata->getUrlForDelete($this->suit->getId());

        $metadata->getClient()->delete($url);
    }

    public function getSuit(): SuitedUpEntity
    {
        return $this->suit;
    }
}

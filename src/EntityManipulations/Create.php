<?php

namespace Awwar\PhpHttpEntityManager\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

class Create implements ManipulationCommandInterface
{
    public function __construct(private SuitedUpEntity $suit)
    {
    }

    public function execute(): void
    {
        $metadata = $this->suit->getMetadata();
        $url = $metadata->getUrlForCreate();
        $data = $this->suit->callBeforeCreate();

        $response = $metadata->getClient()->create($url, $data);

        $this->suit->callAfterCreate($response);
    }

    public function getSuit(): SuitedUpEntity
    {
        return $this->suit;
    }
}

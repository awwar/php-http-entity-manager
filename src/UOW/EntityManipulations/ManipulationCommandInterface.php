<?php

namespace Awwar\PhpHttpEntityManager\UOW\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

interface ManipulationCommandInterface
{
    public function execute(): void;

    public function getSuit(): SuitedUpEntity;
}

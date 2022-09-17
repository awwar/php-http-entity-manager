<?php

namespace Awwar\PhpHttpEntityManager\EntityManipulations;

use Awwar\PhpHttpEntityManager\UOW\SuitedUpEntity;

interface ManipulationCommandInterface
{
    public function execute(): void;

    public function getSuit(): SuitedUpEntity;
}

<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork\EntityManipulations;

use Awwar\PhpHttpEntityManager\UnitOfWork\SuitedUpEntity;

interface ManipulationCommandInterface
{
    public function execute(): void;

    public function getSuit(): SuitedUpEntity;
}

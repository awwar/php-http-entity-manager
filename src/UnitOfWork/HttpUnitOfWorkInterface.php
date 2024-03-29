<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

interface HttpUnitOfWorkInterface
{
    public function clear(string $objectName = null): void;

    public function commit(SuitedUpEntity $suit, bool $withWatch = true): void;

    public function delete(SuitedUpEntity $suit): void;

    public function calculateChanges(): ChangesCollection;

    public function getFromIdentity(SuitedUpEntity $suit): SuitedUpEntity;

    public function hasSuit(SuitedUpEntity $suit): bool;

    public function remove(SuitedUpEntity $suit): void;

    public function upgrade(SuitedUpEntity $suit): void;
}

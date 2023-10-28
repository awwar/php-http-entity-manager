<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

use Awwar\PhpHttpEntityManager\UnitOfWork\EntityManipulations\Create;
use Awwar\PhpHttpEntityManager\UnitOfWork\EntityManipulations\Delete;
use Awwar\PhpHttpEntityManager\UnitOfWork\EntityManipulations\ManipulationCommandInterface;
use Awwar\PhpHttpEntityManager\UnitOfWork\EntityManipulations\Update;

class ChangesCollection
{
    private array $forCreate = [];
    private array $forDelete = [];
    private array $forUpdate = [];

    public function addDelete(string $spl, Delete $action): void
    {
        $this->forDelete[$spl] = $action;
    }

    public function addCreate(string $spl, Create $action): void
    {
        $this->forCreate[$spl] = $action;
    }

    public function addUpdate(string $spl, Update $action): void
    {
        $this->forUpdate[$spl] = $action;
    }

    /**
     * @return ManipulationCommandInterface[]
     */
    public function getAll(): array
    {
        return array_merge($this->forCreate, $this->forUpdate, $this->forDelete);
    }
}

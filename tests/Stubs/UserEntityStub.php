<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Awwar\PhpHttpEntityManager\UnitOfWork\EntityChangesDTO;
use Traversable;

class UserEntityStub
{
    public int $id;
    public string $name = '';

    public static function create(int $id, string $name): self
    {
        $self = new self();

        $self->id = $id;
        $self->name = $name;

        return $self;
    }

    public function relationMappingCallback(): array
    {
        return [$this->name . '-relation'];
    }

    public function updateRequestLayoutCallback(EntityChangesDTO $changesDTO): array
    {
        return [$this->name . '-update'];
    }

    public function createRequestLayoutCallback(EntityChangesDTO $changesDTO): array
    {
        return [$this->name . '-create'];
    }

    public function listMappingCallback(array $data): Traversable
    {
        foreach ($data as $value) {
            yield $this->name . '-' . $value;
        }
    }
}

<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

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

    public function relationMapper(self $self): array
    {
        return [$this->name . '-relation'];
    }

    public function updateLayout(self $self): array
    {
        return [$this->name . '-update'];
    }

    public function createLayout(self $self): array
    {
        return [$this->name . '-create'];
    }

    public function listDetermination(array $data): Traversable
    {
        foreach ($data as $value) {
            yield $this->name . '-' . $value;
        }
    }
}

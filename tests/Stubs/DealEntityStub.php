<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Awwar\PhpHttpEntityManager\Http\Resource\Reference;

class DealEntityStub
{
    public int $id;
    public int $amount = 0;
    public UserEntityStub $user;

    protected function mapper(array &$data, string $name): iterable
    {
        yield new Reference($data['data'][$name]['id']);
    }
}

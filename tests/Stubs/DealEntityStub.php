<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Awwar\PhpHttpEntityManager\Http\Resource\FullData;
use Awwar\PhpHttpEntityManager\Http\Resource\Reference;

class DealEntityStub
{
    public int $id;
    public int $amount = 0;
    public UserEntityStub $user;
    public ?UserEntityStub $admin = null;

    private function mapper(array &$data, string $name): iterable
    {
        if ($name === 'user') {
            yield new Reference($data['data'][$name]['id']);
        }

        if ($name === 'admin' && isset($data['data'][$name]['id'])) {
            yield new FullData(['data' => $data['data'][$name]]);
        }
    }
}

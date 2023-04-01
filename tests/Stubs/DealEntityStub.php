<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Awwar\PhpHttpEntityManager\Collection\Collection;
use Awwar\PhpHttpEntityManager\EntityManager\Resource\FullData;
use Awwar\PhpHttpEntityManager\EntityManager\Resource\Reference;

class DealEntityStub
{
    public int $id;
    public int $amount = 0;
    public UserEntityStub $user;
    public ?UserEntityStub $admin = null;
    public Collection $invoices;

    private function mapper(array &$data, string $name): iterable
    {
        if ($name === 'invoices') {
            foreach ($data['data'][$name] ?? [] as $id) {
                yield new Reference($id);
            }
        }

        if ($name === 'user') {
            yield new Reference($data['data'][$name]['id']);
        }

        if ($name === 'admin' && isset($data['data'][$name]['id'])) {
            yield new FullData(['data' => $data['data'][$name]]);
        }
    }
}

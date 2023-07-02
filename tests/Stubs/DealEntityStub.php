<?php

namespace Awwar\PhpHttpEntityManager\Tests\Stubs;

use Awwar\PhpHttpEntityManager\Collection\CollectionInterface;
use Awwar\PhpHttpEntityManager\EntityManager\RelationUnit\RelationData;
use Awwar\PhpHttpEntityManager\EntityManager\RelationUnit\RelationReference;

class DealEntityStub
{
    public int $id;
    public int $amount = 0;
    public UserEntityStub $user;
    public ?UserEntityStub $admin = null;
    public CollectionInterface $invoices;

    private function mapper(array &$data, string $name): iterable
    {
        if ($name === 'invoices') {
            foreach ($data['data'][$name] ?? [] as $id) {
                yield new RelationReference($id);
            }
        }

        if ($name === 'user') {
            yield new RelationReference($data['data'][$name]['id']);
        }

        if ($name === 'admin' && isset($data['data'][$name]['id'])) {
            yield new RelationData(['data' => $data['data'][$name]]);
        }
    }
}

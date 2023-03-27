<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Client\Client;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\FieldsMetadata;
use Awwar\PhpHttpEntityManager\Tests\Stubs\EntityStub;
use Awwar\PhpHttpEntityManager\Tests\Stubs\HttpClientStub;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    public function testWhenMinimalSetup(): void
    {
        $client = new Client(
            client:       new HttpClientStub(),
            updateMethod: 'PATCH',
            entityName:   'EntityStub'
        );
        $fieldsMetadata = new FieldsMetadata('id');

        $metadata = new EntityMetadata(
            entityClassName: EntityStub::class,
            fieldsMetadata:   $fieldsMetadata,
            client:          $client
        );

        self::assertSame('id', $metadata->getIdProperty());
        self::assertSame(EntityStub::class, $metadata->getClassName());
        self::assertSame('entity_stub', $metadata->getName());
    }
}

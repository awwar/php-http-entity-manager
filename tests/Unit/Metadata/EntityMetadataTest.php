<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\FieldsSettings;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use PHPUnit\Framework\TestCase;

class EntityMetadataTest extends TestCase
{
    public function testWhenMinimalSetup(): void
    {
        $client = $this->createMock(ClientInterface::class);
        $fieldsMetadata = new FieldsSettings('id');

        $metadata = new EntityMetadata(
            entityClassName: UserEntityStub::class,
            fieldsSettings: $fieldsMetadata,
            client: $client
        );

        self::assertSame('id', $metadata->getIdProperty());
        self::assertSame(UserEntityStub::class, $metadata->getClassName());
        self::assertSame('user_entity_stub', $metadata->getName());
        self::assertSame('/api/user_entity_stub/', $metadata->getUrlForCreate());
        self::assertSame('/api/user_entity_stub/11/', $metadata->getUrlForDelete(11));
        self::assertSame('/api/user_entity_stub/', $metadata->getUrlForList());
        self::assertSame('/api/user_entity_stub/12/', $metadata->getUrlForOne(12));
        self::assertSame('/api/user_entity_stub/13/', $metadata->getUrlForUpdate(13));
        self::assertSame($client, $metadata->getClient());
    }
}

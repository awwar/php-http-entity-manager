<?php

namespace Awwar\PhpHttpEntityManager\Tests\Acceptance;

use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\Enum\RelationExpectsEnum;
use Awwar\PhpHttpEntityManager\Http\HttpEntityManager;
use Awwar\PhpHttpEntityManager\Metadata\CallbacksSettings;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\FieldsSettings;
use Awwar\PhpHttpEntityManager\Metadata\MetadataRegistry;
use Awwar\PhpHttpEntityManager\Metadata\RelationSettings;
use Awwar\PhpHttpEntityManager\Tests\Stubs\DealEntityProxyStub;
use Awwar\PhpHttpEntityManager\Tests\Stubs\DealEntityStub;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityProxyStub;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use Awwar\PhpHttpEntityManager\UOW\EntityAtelier;
use Awwar\PhpHttpEntityManager\UOW\HttpUnitOfWork;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityManagerTest extends TestCase
{
    private MockObject $httpClientMock;

    private HttpEntityManager $entityManager;

    public function testFlushWhenCreate(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('create')
            ->with(
                '/api/user_entity_stub/',
                [
                    'data' => [
                        'name' => 'Alyx',
                    ],
                ]
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alyx',
                    ],
                ]
            );

        $entity = new UserEntityStub();
        $entity->name = 'Alyx';

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
    }

    public function testFlushWhenUpdate(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/11/',
                []
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alyx',
                    ],
                ]
            );

        $this->httpClientMock
            ->expects(self::once())
            ->method('update')
            ->with(
                '/api/user_entity_stub/11/',
                [
                    'data' => [
                        'name' => 'Sasha',
                    ],
                ]
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Sasha',
                    ],
                ]
            );

        $entity = $this->entityManager->find(UserEntityStub::class, 11);
        $entity->name = 'Sasha';

        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Sasha', $entity->name);
    }

    public function testFlushWhenUpdateButNothingChanged(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/11/',
                []
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alyx',
                    ],
                ]
            );

        $entity = $this->entityManager->find(UserEntityStub::class, 11);
        $entity->name = 'Alyx';

        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Alyx', $entity->name);
    }

    public function testFlushWhenDelete(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/11/',
                []
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alyx',
                    ],
                ]
            );

        $this->httpClientMock
            ->expects(self::once())
            ->method('delete')
            ->with(
                '/api/user_entity_stub/11/',
                []
            );

        $entity = $this->entityManager->find(UserEntityStub::class, 11);
        $entity->name = 'Sasha';

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Sasha', $entity->name);
    }

    public function testGetWhenRelation(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/deal_entity_stub/11/',
                []
            )->willReturn(
                [
                    'data' => [
                        'id'     => 11,
                        'amount' => 123,
                        'user'   => [
                            'id' => 15,
                        ],
                    ],
                ]
            );

        $entity = $this->entityManager->find(DealEntityStub::class, 11);

        self::assertSame(11, $entity->id);
        self::assertSame(123, $entity->amount);
        self::assertInstanceOf(UserEntityProxyStub::class, $entity->user);
        self::assertSame(15, $entity->user->id);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClientMock = $this->createMock(ClientInterface::class);
        $userFieldsMetadata = new FieldsSettings('id');
        $userFieldsMetadata->addAllCasesDataFieldMap('id', 'data.id');
        $userFieldsMetadata->addAllCasesDataFieldMap('name', 'data.name');

        $userMetadata = new EntityMetadata(
            entityClassName: UserEntityStub::class,
            fieldsSettings: $userFieldsMetadata,
            client: $this->httpClientMock,
            proxyClassName: UserEntityProxyStub::class
        );

        $dealFieldsMetadata = new FieldsSettings('id');
        $dealFieldsMetadata->addAllCasesDataFieldMap('id', 'data.id');
        $dealFieldsMetadata->addAllCasesDataFieldMap('amount', 'data.amount');
        $dealFieldsMetadata->addRelationField('user', new RelationSettings(
            class: UserEntityStub::class,
            name: 'user',
            expects: RelationExpectsEnum::ONE
        ));

        $callbackSetting = new CallbacksSettings(relationMapperMethod: 'mapper');

        $dealMetadata = new EntityMetadata(
            entityClassName: DealEntityStub::class,
            fieldsSettings: $dealFieldsMetadata,
            client: $this->httpClientMock,
            proxyClassName: DealEntityProxyStub::class,
            callbacksSettings: $callbackSetting
        );

        $metadataRegistry = new MetadataRegistry([$userMetadata, $dealMetadata]);
        $entityAtelier = new EntityAtelier($metadataRegistry);
        $uow = new HttpUnitOfWork();
        $this->entityManager = new HttpEntityManager(
            $uow,
            $entityAtelier
        );
    }
}

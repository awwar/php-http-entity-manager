<?php

namespace Awwar\PhpHttpEntityManager\Tests\Acceptance;

use Awwar\PhpHttpEntityManager\Client\Client;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class EntityManagerTest extends TestCase
{
    private MockObject $httpClientMock;

    private HttpEntityManager $entityManager;

    public function testFlushWhenCreate(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(
            [
                'data' => [
                    'id'   => 11,
                    'name' => 'Alyx',
                ],
            ]
        );
        $this->httpClientMock
            ->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                '/api/user_entity_stub/',
                [
                    'json' => [
                        'data' => [
                            'name' => 'Alyx',
                        ],
                    ],
                ]
            )->willReturn($response);

        $entity = new UserEntityStub();
        $entity->name = 'Alyx';

        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
    }

    public function testFlushWhenUpdate(): void
    {
        $responseOnGet = $this->createMock(ResponseInterface::class);
        $responseOnGet->method('toArray')->willReturn(
            [
                'data' => [
                    'id'   => 11,
                    'name' => 'Alyx',
                ],
            ]
        );
        $responseOnPatch = $this->createMock(ResponseInterface::class);
        $responseOnPatch->method('toArray')->willReturn(
            [
                'data' => [
                    'id'   => 11,
                    'name' => 'Sasha',
                ],
            ]
        );
        $this->httpClientMock
            ->expects(self::exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    'GET',
                    '/api/user_entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ],
                [
                    'PATCH',
                    '/api/user_entity_stub/11/',
                    [
                        'json' => [
                            'data' => [
                                'name' => 'Sasha',
                            ],
                        ],
                    ],
                ]
            )->willReturnOnConsecutiveCalls(
                $responseOnGet,
                $responseOnPatch,
            );

        $entity = $this->entityManager->find(UserEntityStub::class, 11);
        $entity->name = 'Sasha';

        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Sasha', $entity->name);
    }

    public function testFlushWhenUpdateButNothingChanged(): void
    {
        $responseOnGet = $this->createMock(ResponseInterface::class);
        $responseOnGet->method('toArray')->willReturn(
            [
                'data' => [
                    'id'   => 11,
                    'name' => 'Alyx',
                ],
            ]
        );
        $this->httpClientMock
            ->expects(self::exactly(1))
            ->method('request')
            ->with(
                'GET',
                '/api/user_entity_stub/11/',
                [
                    'query' => [],
                ],
            )->willReturn($responseOnGet);

        $entity = $this->entityManager->find(UserEntityStub::class, 11);
        $entity->name = 'Alyx';

        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Alyx', $entity->name);
    }

    public function testFlushWhenDelete(): void
    {
        $responseOnGet = $this->createMock(ResponseInterface::class);
        $responseOnGet->method('toArray')->willReturn(
            [
                'data' => [
                    'id'   => 11,
                    'name' => 'Alyx',
                ],
            ]
        );
        $responseOnDelete = $this->createMock(ResponseInterface::class);
        $responseOnDelete->method('toArray')->willReturn(
            []
        );
        $this->httpClientMock
            ->expects(self::exactly(2))
            ->method('request')
            ->withConsecutive(
                [
                    'GET',
                    '/api/user_entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ],
                [
                    'DELETE',
                    '/api/user_entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ]
            )->willReturnOnConsecutiveCalls(
                $responseOnGet,
                $responseOnDelete,
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
        $responseOnGet = $this->createMock(ResponseInterface::class);
        $responseOnGet->method('toArray')->willReturn(
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

        $this->httpClientMock
            ->expects(self::exactly(1))
            ->method('request')
            ->with(
                'GET',
                '/api/deal_entity_stub/11/',
                [
                    'query' => [],
                ],
            )->willReturn(
                $responseOnGet,
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

        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
        $client = new Client(
            client: $this->httpClientMock,
            updateMethod: 'PATCH',
            entityName: 'EntityStub'
        );
        $userFieldsMetadata = new FieldsSettings('id');
        $userFieldsMetadata->addAllCasesDataFieldMap('id', 'data.id');
        $userFieldsMetadata->addAllCasesDataFieldMap('name', 'data.name');

        $userMetadata = new EntityMetadata(
            entityClassName: UserEntityStub::class,
            fieldsSettings: $userFieldsMetadata,
            client: $client,
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
            client: $client,
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

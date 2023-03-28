<?php

namespace Awwar\PhpHttpEntityManager\Tests\Acceptance;

use Awwar\PhpHttpEntityManager\Client\Client;
use Awwar\PhpHttpEntityManager\Http\HttpEntityManager;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\FieldsSettings;
use Awwar\PhpHttpEntityManager\Metadata\MetadataRegistry;
use Awwar\PhpHttpEntityManager\Tests\Stubs\EntityStub;
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
                '/api/entity_stub/',
                [
                    'json' => [
                        'data' => [
                            'name' => 'Alyx',
                        ],
                    ],
                ]
            )->willReturn($response);

        $entity = new EntityStub();
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
                    '/api/entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ],
                [
                    'PATCH',
                    '/api/entity_stub/11/',
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

        $entity = $this->entityManager->find(EntityStub::class, 11);
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
                '/api/entity_stub/11/',
                [
                    'query' => [],
                ],
            )->willReturn($responseOnGet);

        $entity = $this->entityManager->find(EntityStub::class, 11);
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
                    '/api/entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ],
                [
                    'DELETE',
                    '/api/entity_stub/11/',
                    [
                        'query' => [],
                    ],
                ]
            )->willReturnOnConsecutiveCalls(
                $responseOnGet,
                $responseOnDelete,
            );

        $entity = $this->entityManager->find(EntityStub::class, 11);
        $entity->name = 'Sasha';

        $this->entityManager->remove($entity);
        $this->entityManager->flush();

        self::assertSame(11, $entity->id);
        self::assertSame('Sasha', $entity->name);
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
        $fieldsMetadata = new FieldsSettings('id');
        $fieldsMetadata->addAllCasesDataFieldMap('id', 'data.id');
        $fieldsMetadata->addAllCasesDataFieldMap('name', 'data.name');

        $metadata = new EntityMetadata(
            entityClassName: EntityStub::class,
            fieldsSettings: $fieldsMetadata,
            client: $client
        );

        $metadataRegistry = new MetadataRegistry([$metadata]);
        $entityAtelier = new EntityAtelier($metadataRegistry);
        $uow = new HttpUnitOfWork();
        $this->entityManager = new HttpEntityManager(
            $uow,
            $entityAtelier
        );
    }
}

<?php

namespace Awwar\PhpHttpEntityManager\Tests\Integrational\Repository;

use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\EntityManager\HttpEntityManager;
use Awwar\PhpHttpEntityManager\Exception\NotFoundException;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\FieldsSettings;
use Awwar\PhpHttpEntityManager\Metadata\MetadataRegistry;
use Awwar\PhpHttpEntityManager\Repository\HttpRepository;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityProxyStub;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use Awwar\PhpHttpEntityManager\UnitOfWork\EntityAtelier;
use Awwar\PhpHttpEntityManager\UnitOfWork\HttpUnitOfWork;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class HttpRepositoryTest extends TestCase
{
    private HttpRepository $repository;

    private MockObject $httpClientMock;

    public function testFind(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/11/',
                [
                    'a' => [
                        'b',
                        'c',
                    ],
                ]
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alex',
                    ],
                ]
            );

        $user = $this->repository->find(11, ['a' => ['b', 'c']]);

        self::assertSame(11, $user->id);
        self::assertSame('Alex', $user->name);
    }

    public function testFindWhenNotFound(): void
    {
        $exception = new NotFoundException();

        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/11/',
                [
                    'a' => [
                        'b',
                        'c',
                    ],
                ]
            )->willThrowException($exception);

        self::expectExceptionObject($exception);

        $this->repository->find(11, ['a' => ['b', 'c']]);
    }

    public function testAddWhenWithoutFlush(): void
    {
        $this->httpClientMock->expects(self::never())->method(self::anything());

        $user = new UserEntityStub();
        $user->name = 'Alex';

        $this->repository->add($user);
    }

    public function testAddWhenWithFlush(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('create')
            ->with(
                '/api/user_entity_stub/',
                [
                    'data' => [
                        'name' => 'Alex',
                    ],
                ]
            )->willReturn(
                [
                    'data' => [
                        'id'   => 11,
                        'name' => 'Alex',
                    ],
                ]
            );

        $user = new UserEntityStub();
        $user->name = 'Alex';

        $this->repository->add($user, flush: true);
    }

    public function testAddWhenWithFlushAndUpdate(): void
    {
        $this->httpClientMock
            ->expects(self::never())
            ->method(self::anything());

        $user = new UserEntityStub();
        $user->id = 11;
        $user->name = 'Alex';

        $this->repository->add($user, flush: true);
    }

    public function testRemove(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('delete')
            ->with('/api/user_entity_stub/11/', []);

        $user = new UserEntityStub();
        $user->id = 11;
        $user->name = 'Alex';

        $this->repository->add($user);

        $this->repository->remove($user, flush: true);
    }

    public function testRemoveWhenNotPersisted(): void
    {
        $this->httpClientMock
            ->expects(self::never())
            ->method(self::anything());

        $user = new UserEntityStub();
        $user->id = 11;
        $user->name = 'Alex';

        self::expectExceptionMessage(
            "Identity of class Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub with id 11 not found"
        );

        $this->repository->remove($user, flush: true);
    }

    public function testRemoveWhenEntityIsNew(): void
    {
        $this->httpClientMock
            ->expects(self::never())
            ->method(self::anything());

        $user = new UserEntityStub();
        $user->name = 'Alex';

        $this->repository->add($user);

        self::expectExceptionMessage("Unable to remove new entity");

        $this->repository->remove($user, flush: true);
    }

    public function testDoubleRemove(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('delete')
            ->with('/api/user_entity_stub/11/', []);

        $user = new UserEntityStub();
        $user->id = 11;
        $user->name = 'Alex';

        $this->repository->add($user);

        self::expectExceptionMessage(
            "Identity of class Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub with id 11 not found"
        );

        $this->repository->remove($user, flush: true);
        $this->repository->remove($user, flush: true);
    }

    public function testFilterOne(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/',
                [
                    'a' => 1,
                ]
            )->willReturn([]);

        self::expectExceptionMessage('user_entity_stub not found');

        $this->repository->filterOne(['a' => 1]);
    }

    public function testFilter(): void
    {
        $this->httpClientMock
            ->expects(self::once())
            ->method('get')
            ->with(
                '/api/user_entity_stub/',
                [
                    'a' => 1,
                ]
            )->willReturn([]);

        $generator = $this->repository->filter(['a' => 1]);

        self::assertInstanceOf(Generator::class, $generator);
        self::assertNull($generator->current());
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

        $metadataRegistry = new MetadataRegistry([$userMetadata]);
        $entityAtelier = new EntityAtelier($metadataRegistry);
        $uow = new HttpUnitOfWork();
        $entityManager = new HttpEntityManager($uow, $entityAtelier);

        $this->repository = new HttpRepository($entityManager, UserEntityStub::class);
    }
}

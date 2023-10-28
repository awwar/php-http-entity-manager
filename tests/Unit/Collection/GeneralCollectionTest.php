<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Collection;

use Awwar\PhpHttpEntityManager\Collection\CollectionInterface;
use Awwar\PhpHttpEntityManager\Collection\GeneralCollection;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use PHPUnit\Framework\TestCase;

class GeneralCollectionTest extends TestCase
{
    private CollectionInterface $collection;

    public function testEach(): void
    {
        $count = 0;

        $this->collection->each(
            function (int $i, UserEntityStub $value) use (&$count) {
                $count++;

                self::assertIsInt($i);
                self::assertInstanceOf(UserEntityStub::class, $value);
            }
        );

        self::assertSame(3, $count);
    }

    public function testIsEmptyWhenNotEmpty(): void
    {
        self::assertSame(false, $this->collection->isEmpty());
    }

    public function testIsEmptyWhenEmpty(): void
    {
        $collection = new GeneralCollection();

        self::assertSame(true, $collection->isEmpty());
    }

    public function testFirstWhenExist(): void
    {
        self::assertEquals(UserEntityStub::create(11, 'Alyx'), $this->collection->first());
    }

    public function testFirstWhenNotExist(): void
    {
        $collection = new GeneralCollection();

        self::assertEquals(null, $collection->first());
    }

    public function testLastWhenExist(): void
    {
        self::assertEquals(UserEntityStub::create(13, 'Ben'), $this->collection->last());
    }

    public function testLastWhenNotExist(): void
    {
        $collection = new GeneralCollection();

        self::assertEquals(null, $collection->last());
    }

    public function testMap(): void
    {
        $map = $this->collection->map(function (int $i, UserEntityStub $value) {
            return $value->name;
        });

        self::assertSame(
            [
                'Alyx',
                'Jon',
                'Ben',
            ],
            $map
        );
    }

    public function testFilter(): void
    {
        $filtered = $this->collection->filter(function (int $i, UserEntityStub $value) {
            return mb_strlen($value->name) === 3;
        });

        self::assertEquals(
            new GeneralCollection(
                [
                    1 => UserEntityStub::create(12, 'Jon'),
                    2 => UserEntityStub::create(13, 'Ben'),
                ]
            ),
            $filtered
        );
    }

    public function testFindWhenExist(): void
    {
        $item = $this->collection->find(fn (int $i, UserEntityStub $value) => $value->name === 'Ben');

        self::assertEquals(
            UserEntityStub::create(13, 'Ben'),
            $item
        );
    }

    public function testFindWhenNotExist(): void
    {
        $item = $this->collection->find(fn (int $i, UserEntityStub $value) => $value->name === ':user_name:', 'wops');

        self::assertEquals(
            'wops',
            $item
        );
    }

    public function testReduce(): void
    {
        $value = $this->collection->reduce(
            callback: fn (int $i, UserEntityStub $value, string $acc) => $acc . $value->name,
            accumulator: ''
        );

        self::assertSame('AlyxJonBen', $value);
    }

    public function testCountOf(): void
    {
        $countOf = $this->collection->countOf(function (int $i, UserEntityStub $value) {
            return mb_strlen($value->name) === 3;
        });

        self::assertSame(2, $countOf);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->collection = new GeneralCollection(
            [
                UserEntityStub::create(11, 'Alyx'),
                UserEntityStub::create(12, 'Jon'),
                UserEntityStub::create(13, 'Ben'),
            ]
        );
    }
}

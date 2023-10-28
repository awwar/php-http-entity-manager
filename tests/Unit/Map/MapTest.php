<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Map;

use Awwar\PhpHttpEntityManager\DataStructure\SmartMap;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testPutWhenEmpty(): void
    {
        $map = new SmartMap([]);

        $map->put('a.b.c', 11);

        self::assertSame(
            [
                'a' => [
                    'b' => [
                        'c' => 11,
                    ],
                ],
            ],
            $map->toArray()
        );
    }

    public function testPutWhenFilled(): void
    {
        $map = new SmartMap([
            'a' => [
                'b' => [
                    'c' => 12,
                ],
            ],
        ],);

        $map->put('a.b.c', 11);

        self::assertSame(
            [
                'a' => [
                    'b' => [
                        'c' => 11,
                    ],
                ],
            ],
            $map->toArray()
        );
    }

    public function testGetWhenExists(): void
    {
        $map = new SmartMap([
            'a' => [
                'b' => [
                    'c' => 12,
                ],
            ],
        ],);

        $value = $map->get('a.b.c', 11);

        self::assertSame(12, $value);
    }

    public function testGetWhenNotExists(): void
    {
        $map = new SmartMap([]);

        $value = $map->get('a.b.c', 11);

        self::assertSame(11, $value);
    }
}

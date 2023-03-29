<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Metadata\FilterSettings;
use PHPUnit\Framework\TestCase;

class FilterSettingsTest extends TestCase
{
    public function testCreateWhenEmpty(): void
    {
        $urlSettings = new FilterSettings();

        self::assertSame([], $urlSettings->getFilterQuery());
        self::assertSame([], $urlSettings->getGetOneQuery());
        self::assertSame([], $urlSettings->getFilterOneQuery());
    }

    public function testCreateWhenFilled(): void
    {
        $urlSettings = new FilterSettings(
            filterQuery: ['filterQuery'],
            getOneQuery: ['getOneQuery'],
            filterOneQuery: ['filterOneQuery']
        );

        self::assertSame(['filterQuery'], $urlSettings->getFilterQuery());
        self::assertSame(['getOneQuery'], $urlSettings->getGetOneQuery());
        self::assertSame(['filterOneQuery'], $urlSettings->getFilterOneQuery());
    }
}

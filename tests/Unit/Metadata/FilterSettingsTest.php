<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Metadata\FilterSettings;
use PHPUnit\Framework\TestCase;

class FilterSettingsTest extends TestCase
{
    public function testCreateWhenEmpty(): void
    {
        $urlSettings = new FilterSettings();

        self::assertSame([], $urlSettings->getOnFilterQueryMixin());
        self::assertSame([], $urlSettings->getOnGetOneQueryMixin());
        self::assertSame([], $urlSettings->getOnFindOneQueryMixin());
    }

    public function testCreateWhenFilled(): void
    {
        $urlSettings = new FilterSettings(
            onFilterQueryMixin: ['onFilterQueryMixin'],
            onGetOneQueryMixin: ['onGetOneQueryMixin'],
            onFindOneQueryMixin: ['onFindOneQueryMixin']
        );

        self::assertSame(['onFilterQueryMixin'], $urlSettings->getOnFilterQueryMixin());
        self::assertSame(['onGetOneQueryMixin'], $urlSettings->getOnGetOneQueryMixin());
        self::assertSame(['onFindOneQueryMixin'], $urlSettings->getOnFindOneQueryMixin());
    }
}

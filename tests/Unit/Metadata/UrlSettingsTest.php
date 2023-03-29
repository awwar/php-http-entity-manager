<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Metadata\UrlSettings;
use PHPUnit\Framework\TestCase;

class UrlSettingsTest extends TestCase
{
    public function testCreate(): void
    {
        $urlSettings = new UrlSettings(
            one: "/one/",
            list: "/list/",
            create: "/create/",
            update: "/update/",
            delete: "/delete/"
        );

        self::assertSame("/one/", $urlSettings->getOne());
        self::assertSame("/list/", $urlSettings->getList());
        self::assertSame("/create/", $urlSettings->getCreate());
        self::assertSame("/update/", $urlSettings->getUpdate());
        self::assertSame("/delete/", $urlSettings->getDelete());
    }
}

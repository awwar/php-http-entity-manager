<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Metadata\CallbacksSettings;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use PHPUnit\Framework\TestCase;

class CallbacksSettingsTest extends TestCase
{
    public function testCreateWhenEmpty(): void
    {
        $urlSettings = new CallbacksSettings();
        $userEntity = new UserEntityStub();
        $userEntity->id = 2;
        $userEntity->name = 'Jane';

        self::assertSame([], $urlSettings->getRelationMapper()->call($userEntity, $userEntity));
        self::assertSame([], $urlSettings->getUpdateLayout()->call($userEntity, $userEntity));
        self::assertSame([], $urlSettings->getCreateLayout()->call($userEntity, $userEntity));
        $generator = $urlSettings->getListDetermination()->call($userEntity, ['a', 'b']);
        self::assertSame(['a', 'b'], iterator_to_array($generator));
    }

    public function testCreateWhenFilled(): void
    {
        $urlSettings = new CallbacksSettings(
            relationMapperMethod: 'relationMapper',
            createLayoutMethod: 'createLayout',
            updateLayoutMethod: 'updateLayout',
            listDeterminationMethod: 'listDetermination',
        );
        $userEntity = new UserEntityStub();
        $userEntity->id = 2;
        $userEntity->name = 'Jane';

        self::assertSame(['Jane-relation'], $urlSettings->getRelationMapper()->call($userEntity, $userEntity));
        self::assertSame(['Jane-update'], $urlSettings->getUpdateLayout()->call($userEntity, $userEntity));
        self::assertSame(['Jane-create'], $urlSettings->getCreateLayout()->call($userEntity, $userEntity));
        $generator = $urlSettings->getListDetermination()->call($userEntity, ['a', 'b']);
        self::assertSame(['Jane-a', 'Jane-b'], iterator_to_array($generator));
    }
}

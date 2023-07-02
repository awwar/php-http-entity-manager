<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Metadata\CallbacksSettings;
use Awwar\PhpHttpEntityManager\Tests\Stubs\UserEntityStub;
use Awwar\PhpHttpEntityManager\UnitOfWork\EntityChangesDTO;
use PHPUnit\Framework\TestCase;

class CallbacksSettingsTest extends TestCase
{
    public function testCreateWhenEmpty(): void
    {
        $urlSettings = new CallbacksSettings();
        $userEntity = new UserEntityStub();
        $userEntity->id = 2;
        $userEntity->name = 'Jane';

        $changes = new EntityChangesDTO([], [], [], []);

        self::assertSame([], $urlSettings->getRelationMappingCallback()->call($userEntity));
        self::assertSame([], $urlSettings->getUpdateRequestLayoutCallback()->call($userEntity, $changes));
        self::assertSame([], $urlSettings->getCreateRequestLayoutCallback()->call($userEntity, $changes));
        $generator = $urlSettings->getListMappingCallback()->call($userEntity, ['a', 'b']);
        self::assertSame(['a', 'b'], iterator_to_array($generator));
    }

    public function testCreateWhenFilled(): void
    {
        $urlSettings = new CallbacksSettings(
            relationMappingCallbackMethod: 'relationMappingCallback',
            createRequestLayoutCallbackMethod: 'createRequestLayoutCallback',
            updateRequestLayoutCallbackMethod: 'updateRequestLayoutCallback',
            listMappingCallbackMethod: 'listMappingCallback',
        );
        $userEntity = new UserEntityStub();
        $userEntity->id = 2;
        $userEntity->name = 'Jane';

        $changes = new EntityChangesDTO([], [], [], []);

        self::assertSame(['Jane-relation'], $urlSettings->getRelationMappingCallback()->call($userEntity));
        self::assertSame(['Jane-update'], $urlSettings->getUpdateRequestLayoutCallback()->call($userEntity, $changes));
        self::assertSame(['Jane-create'], $urlSettings->getCreateRequestLayoutCallback()->call($userEntity, $changes));
        $generator = $urlSettings->getListMappingCallback()->call($userEntity, ['a', 'b']);
        self::assertSame(['Jane-a', 'Jane-b'], iterator_to_array($generator));
    }
}

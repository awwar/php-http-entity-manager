<?php

namespace Awwar\PhpHttpEntityManager\Tests\Unit\Metadata;

use Awwar\PhpHttpEntityManager\Enum\RelationExpectsEnum;
use Awwar\PhpHttpEntityManager\Metadata\FieldsSettings;
use Awwar\PhpHttpEntityManager\Metadata\RelationSettings;
use PHPUnit\Framework\TestCase;

class FieldsSettingsTest extends TestCase
{
    public function testCreateFilled(): void
    {
        $key6Setting = new RelationSettings(
            'some_class',
            'some_class_relation',
            RelationExpectsEnum::ONE
        );

        $fieldsSettings = new FieldsSettings('id');
        $fieldsSettings->addAllCasesDataFieldMap('key1', 'data.key1');
        $fieldsSettings->addOnResponseDataFieldMap('key2', 'data.key2');
        $fieldsSettings->addOnRequestDataFieldMap('key3', 'data.key3');
        $fieldsSettings->addDataFieldMap('afterRead', 'key4', 'data.key4');
        $fieldsSettings->addDefaultValue('key5', '1234');
        $fieldsSettings->addRelationField('key6', $key6Setting);

        self::assertSame('id', $fieldsSettings->getIdProperty());
        self::assertSame(
            [
                'beforeCreate' => [
                    'key1' => 'data.key1',
                    'key3' => 'data.key3',
                ],
                'beforeUpdate' => [
                    'key1' => 'data.key1',
                    'key3' => 'data.key3',
                ],
                'afterRead'    => [
                    'key1' => 'data.key1',
                    'key2' => 'data.key2',
                    'key4' => 'data.key4',
                ],
                'afterCreate'  => [
                    'key1' => 'data.key1',
                    'key2' => 'data.key2',
                ],
                'afterUpdate'  => [
                    'key1' => 'data.key1',
                    'key2' => 'data.key2',
                ],
            ],
            $fieldsSettings->getDataFieldsSettings()
        );
        self::assertSame(
            [
                'key5' => '1234',
            ],
            $fieldsSettings->getDefaultValues()
        );
        self::assertSame(
            ['key1', 'key2', 'key3', 'key4', 'key6'],
            $fieldsSettings->getAllProperties()
        );
        self::assertSame(
            ['key1', 'key2', 'key3', 'key4'],
            $fieldsSettings->getScalarProperties()
        );
        self::assertSame(
            ['key6' => $key6Setting],
            $fieldsSettings->getRelationProperties()
        );
    }
}

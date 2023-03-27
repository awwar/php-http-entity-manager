<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use InvalidArgumentException;

class FieldsMetadata
{
    const AVAILABLE_CONDITIONS = [
        'afterRead',
        'afterRead',
        'afterCreate',
        'afterUpdate',
        'beforeCreate',
        'beforeUpdate',
    ];

    private array $relationFields = [];

    private array $dataFields = [];

    private array $defaultValues = [];

    private array $properties = [];

    private array $scalarProperties = [];

    public function addDataField(string $condition, string $fieldName, ?string $path): void
    {
        if (in_array($condition, self::AVAILABLE_CONDITIONS)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Field %s must be one of %s',
                    '$condition',
                    join(', ', self::AVAILABLE_CONDITIONS)
                )
            );
        }

        $this->scalarProperties[$fieldName] = $fieldName;
        $this->properties[$fieldName] = $fieldName;

        if ($path === null) {
            return;
        }

        $this->dataFields[$condition][$fieldName] = $path;
    }
}
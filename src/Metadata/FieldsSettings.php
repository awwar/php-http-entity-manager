<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use InvalidArgumentException;

class FieldsSettings
{
    public const REQUEST_CONDITIONS = [
        'beforeCreate',
        'beforeUpdate',
    ];

    public const RESPONSE_CONDITIONS = [
        'afterRead',
        'afterCreate',
        'afterUpdate',
    ];

    public const AVAILABLE_CONDITIONS = [
        ...self::REQUEST_CONDITIONS,
        ...self::RESPONSE_CONDITIONS,
    ];

    private array $dataFieldsSettings = [];

    private array $defaultValues = [];

    private array $allProperties = [];

    private array $scalarProperties = [];

    private array $relationProperties = [];

    public function __construct(private string $idProperty)
    {
    }

    public function addAllCasesDataFieldMap(string $fieldName, ?string $path): void
    {
        $this->addOnRequestDataFieldMap($fieldName, $path);
        $this->addOnResponseDataFieldMap($fieldName, $path);
    }

    public function addOnRequestDataFieldMap(string $fieldName, ?string $path): void
    {
        foreach (self::REQUEST_CONDITIONS as $condition) {
            $this->addDataFieldMap($condition, $fieldName, $path);
        }
    }

    public function addDataFieldMap(string $condition, string $fieldName, ?string $path): void
    {
        if (isset($this->relationProperties[$fieldName])) {
            throw new InvalidArgumentException(
                sprintf('%s field already defined in relations', $fieldName)
            );
        }

        if (in_array($condition, self::AVAILABLE_CONDITIONS) === false) {
            throw new InvalidArgumentException(
                sprintf(
                    'Field %s must be one of %s',
                    '$condition',
                    join(', ', self::AVAILABLE_CONDITIONS)
                )
            );
        }

        $this->scalarProperties[$fieldName] = $fieldName;
        $this->allProperties[$fieldName] = $fieldName;

        if ($path === null) {
            return;
        }

        $this->dataFieldsSettings[$condition][$fieldName] = $path;
    }

    public function addOnResponseDataFieldMap(string $fieldName, ?string $path): void
    {
        foreach (self::RESPONSE_CONDITIONS as $condition) {
            $this->addDataFieldMap($condition, $fieldName, $path);
        }
    }

    public function addDefaultValue(string $fieldName, mixed $value): void
    {
        $this->defaultValues[$fieldName] = $value;
    }

    public function addRelationField(string $fieldName, RelationSettings $setting): void
    {
        if (isset($this->scalarProperties[$fieldName])) {
            throw new InvalidArgumentException(
                sprintf('%s field already defined in relations', $fieldName)
            );
        }

        $this->relationProperties[$fieldName] = $setting;
        $this->allProperties[$fieldName] = $fieldName;
    }

    public function getAllProperties(): array
    {
        return array_values($this->allProperties);
    }

    public function getDataFieldsSettings(): array
    {
        return $this->dataFieldsSettings;
    }

    public function getDefaultValues(): array
    {
        return $this->defaultValues;
    }

    public function getIdProperty(): string
    {
        return $this->idProperty;
    }

    public function getRelationProperties(): array
    {
        return $this->relationProperties;
    }

    public function getScalarProperties(): array
    {
        return array_values($this->scalarProperties);
    }
}

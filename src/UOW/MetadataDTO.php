<?php

namespace Awwar\PhpHttpEntityManager\UOW;

use Exception;

class MetadataDTO
{
    private array $dataFields = [];
    private array $relationFields = [];
    private array $defaultValues = [];
    private array $properties = [];
    private array $scalarProperties = [];

    public function __construct(
        private string $entityClassName,
        private string $proxyClassName,
        private ?string $idProperty,
        private string $updateMethod,
        private bool $useDiffOnUpdate,
        private array $filterQuery,
        private array $getOneQuery,
        private array $filterOneQuery,
        private string $name,
        private mixed $httpClient,
        private mixed $repository,
        private string $getOnePattern,
        private string $getListPattern,
        private string $createPattern,
        private string $updatePattern,
        private string $deletePattern,
        array $dataFields,
        array $relationFields,
        array $defaultValues,
        private string $relationMapperMethod,
        private string $createLayoutMethod,
        private string $updateLayoutMethod,
        private string $listDeterminationMethod,
    ) {
        if ($this->idProperty === null) {
            throw new Exception("The EntityId annotation must be set on http entity!");
        }
        foreach ($dataFields as $map) {
            $field = $map['targetName'];
            foreach ($map['data'] as $condition => $path) {
                if ($path === null) {
                    continue;
                }
                $this->dataFields[$condition][$field] = $path;
            }
            $this->scalarProperties[] = $field;
            $this->properties[] = $field;
        }
        foreach ($relationFields as $map) {
            $field = $map['targetName'];

            $this->relationFields[$field] = RelationMapping::create($map['data']);
            $this->properties[] = $field;
        }
        foreach ($defaultValues as $map) {
            $field = $map['targetName'];

            $this->defaultValues[$field] = $map['data']['value'];
        }
    }

    /**
     * @return array
     */
    public function getScalarProperties(): array
    {
        return $this->scalarProperties;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getEntityClassName(): string
    {
        return $this->entityClassName;
    }

    /**
     * @return string
     */
    public function getProxyClassName(): string
    {
        return $this->proxyClassName;
    }

    public function getIdProperty(): string
    {
        return $this->idProperty;
    }

    /**
     * @return string
     */
    public function getUpdateMethod(): string
    {
        return $this->updateMethod;
    }

    /**
     * @return bool
     */
    public function isUseDiffOnUpdate(): bool
    {
        return $this->useDiffOnUpdate;
    }

    public function getFilterQuery(): array
    {
        return $this->filterQuery;
    }

    public function getGetOneQuery(): array
    {
        return $this->getOneQuery;
    }

    public function getFilterOneQuery(): array
    {
        return $this->filterOneQuery;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getHttpClient(): mixed
    {
        return $this->httpClient;
    }

    public function getRepository(): mixed
    {
        return $this->repository;
    }

    public function getGetOnePattern(): string
    {
        return $this->getOnePattern;
    }

    public function getGetListPattern(): string
    {
        return $this->getListPattern;
    }

    public function getCreatePattern(): string
    {
        return $this->createPattern;
    }

    /**
     * @return string
     */
    public function getUpdatePattern(): string
    {
        return $this->updatePattern;
    }

    /**
     * @return string
     */
    public function getDeletePattern(): string
    {
        return $this->deletePattern;
    }

    /**
     * @return array
     */
    public function getDataFields(): array
    {
        return $this->dataFields;
    }

    /**
     * @return array
     */
    public function getRelationFields(): array
    {
        return $this->relationFields;
    }

    /**
     * @return array
     */
    public function getDefaultValues(): array
    {
        return $this->defaultValues;
    }

    /**
     * @return string
     */
    public function getRelationMapperMethod(): string
    {
        return $this->relationMapperMethod;
    }

    /**
     * @return string
     */
    public function getCreateLayoutMethod(): string
    {
        return $this->createLayoutMethod;
    }

    /**
     * @return string
     */
    public function getUpdateLayoutMethod(): string
    {
        return $this->updateLayoutMethod;
    }

    /**
     * @return string
     */
    public function getListDeterminationMethod(): string
    {
        return $this->listDeterminationMethod;
    }
}
<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Awwar\PhpHttpEntityManager\Client\Client;
use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\Http\HttpRepositoryInterface;
use Awwar\PhpHttpEntityManager\UOW\RelationMapping;
use Closure;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class EntityMetadata
{
    private ClientInterface $client;

    private Closure $createLayout;

    private Closure $updateLayout;

    private Closure $relationMapper;

    private Closure $listDetermination;

    private object $emptyInstance;

    private object $proxy;

    private array $dataFields = [];

    private array $relationFields = [];

    private array $defaultValues = [];

    private array $properties = [];

    private array $scalarProperties = [];

    /**
     * @throws ReflectionException
     */
    public function __construct(
        private string $name,
        private string $idProperty,
        string $entityClassName,
        string $proxyClassName,
        string $updateMethod,
        private bool $useDiffOnUpdate,
        private array $filterQuery,
        private array $getOneQuery,
        private array $filterOneQuery,
        mixed $httpClient,
        private ?HttpRepositoryInterface $repository,
        private UrlMetadata $urlMetadata,
        array $dataFields,
        array $relationFields,
        array $defaultValues,
        private string $relationMapperMethod,
        private string $createLayoutMethod,
        private string $updateLayoutMethod,
        private string $listDeterminationMethod,
    ) {
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

        $this->proxy = (new ReflectionClass($proxyClassName))
            ->newInstanceWithoutConstructor();

        $this->emptyInstance = (new ReflectionClass($entityClassName))
            ->newInstanceWithoutConstructor();

        $this->client = new Client(
            $httpClient,
            $updateMethod,
            $this->name
        );

        if ($methodName = $this->relationMapperMethod) {
            $this->relationMapper = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->relationMapper = function () {
                return [];
            };
        }

        if ($methodName = $this->createLayoutMethod) {
            $this->createLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->createLayout = function () {
                return [];
            };
        }

        if ($methodName = $this->updateLayoutMethod) {
            $this->updateLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->updateLayout = function () {
                return [];
            };
        }

        if ($methodName = $this->listDeterminationMethod) {
            $this->listDetermination = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->listDetermination = function (array $payload) {
                foreach ($payload as $elem) {
                    yield $elem;
                }
            };
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUseDiffOnUpdate(): bool
    {
        return $this->useDiffOnUpdate;
    }

    public function getRepository(): ?HttpRepositoryInterface
    {
        return $this->repository;
    }

    public function getCreateLayout(): callable
    {
        return $this->createLayout;
    }

    public function getUpdateLayout(): callable
    {
        return $this->updateLayout;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getIdProperty(): string
    {
        return $this->idProperty;
    }

    public function getUrlForCreate(): string
    {
        return $this->urlMetadata->getCreate();
    }

    public function getUrlForList(): string
    {
        return $this->urlMetadata->getList();
    }

    public function getUrlForOne(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlMetadata->getOne());
    }

    public function getUrlForUpdate(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlMetadata->getUpdate());
    }

    public function getUrlForDelete(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlMetadata->getDelete());
    }

    public function getFieldMap(string $name): array
    {
        return $this->dataFields[$name] ?? [];
    }

    public function getDefaultValue(string $property): mixed
    {
        $defaults = $this->defaultValues;

        if (array_key_exists($property, $defaults) === false) {
            return null;
        }

        return $defaults[$property];
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getScalarProperties(): array
    {
        return $this->scalarProperties;
    }

    /**
     * @return RelationMapping[]
     */
    public function getRelationsMapping(): array
    {
        return $this->relationFields;
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

    public function getRelationsMapper(): callable
    {
        return $this->relationMapper->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException("Unable to bind relationMapper");
    }

    public function getListDetermination(): callable
    {
        return $this->listDetermination->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException("Unable to bind listDetermination");
    }

    public function getEmptyInstance(): object
    {
        return clone $this->emptyInstance;
    }

    public function getProxy(): object
    {
        return clone $this->proxy;
    }

    public function getProxyClass(): string
    {
        return get_class($this->proxy);
    }

    public function getClassName(): string
    {
        return get_class($this->emptyInstance);
    }
}

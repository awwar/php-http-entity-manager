<?php

namespace Awwar\PhpHttpEntityManager\UOW;

use Awwar\PhpHttpEntityManager\Http\HttpRepositoryInterface;
use Closure;
use ReflectionClass;
use ReflectionException;

class EntityMetadata
{
    private ClientInterface $client;

    private Closure $createLayout;

    private Closure $updateLayout;

    private Closure $relationMapper;

    private Closure $listDetermination;

    private object $emptyInstance;

    private object $proxy;

    /**
     * @param MetadataDTO $metadataDTO
     * @throws ReflectionException
     */
    public function __construct(private MetadataDTO $metadataDTO)
    {
        $this->proxy = (new ReflectionClass($metadataDTO->getProxyClassName()))
            ->newInstanceWithoutConstructor();

        $this->emptyInstance = (new ReflectionClass($metadataDTO->getEntityClassName()))
            ->newInstanceWithoutConstructor();

        $this->client = new Client(
            $metadataDTO->getHttpClient(),
            $metadataDTO->getUpdateMethod(),
            $metadataDTO->getName()
        );

        if ($methodName = $metadataDTO->getRelationMapperMethod()) {
            $this->relationMapper = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->relationMapper = function (...$payload) {
                return [];
            };
        }

        if ($methodName = $metadataDTO->getCreateLayoutMethod()) {
            $this->createLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->createLayout = function (...$payload) {
                return [];
            };
        }

        if ($methodName = $metadataDTO->getUpdateLayoutMethod()) {
            $this->updateLayout = (function (...$payload) use ($methodName) {
                return $this->{$methodName}(...$payload);
            })->bindTo($this->emptyInstance, $this->emptyInstance);
        } else {
            $this->updateLayout = function (...$payload) {
                return [];
            };
        }

        if ($methodName = $metadataDTO->getListDeterminationMethod()) {
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
        return $this->metadataDTO->getName();
    }

    public function isUseDiffOnUpdate(): bool
    {
        return $this->metadataDTO->isUseDiffOnUpdate();
    }

    public function getRepository(): ?HttpRepositoryInterface
    {
        return $this->metadataDTO->getRepository();
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
        return $this->metadataDTO->getIdProperty();
    }

    public function getUrlForCreate(): string
    {
        return $this->metadataDTO->getCreatePattern();
    }

    public function getUrlForList(): string
    {
        return $this->metadataDTO->getGetListPattern();
    }

    public function getUrlForOne(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->metadataDTO->getGetOnePattern());
    }

    public function getUrlForUpdate(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->metadataDTO->getUpdatePattern());
    }

    public function getUrlForDelete(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->metadataDTO->getUpdatePattern());
    }

    public function getFieldMap(string $name): array
    {
        return $this->metadataDTO->getDataFields()[$name] ?? [];
    }

    public function getDefaultValue(string $property): mixed
    {
        $defaults = $this->metadataDTO->getDefaultValues();

        if (array_key_exists($property, $defaults) === false) {
            return null;
        }

        return $defaults[$property];
    }

    public function getProperties(): array
    {
        return $this->metadataDTO->getProperties();
    }

    public function getScalarProperties(): array
    {
        return $this->metadataDTO->getScalarProperties();
    }

    /**
     * @return RelationMapping[]
     */
    public function getRelationsMapping(): array
    {
        return $this->metadataDTO->getRelationFields();
    }

    public function getFilterQuery(): array
    {
        return $this->metadataDTO->getFilterQuery();
    }

    public function getGetOneQuery(): array
    {
        return $this->metadataDTO->getGetOneQuery();
    }

    public function getFilterOneQuery(): array
    {
        return $this->metadataDTO->getFilterOneQuery();
    }

    public function getRelationsMapper(): callable
    {
        return $this->relationMapper->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new \RuntimeException("Unable to bind relationMapper");
    }

    public function getListDetermination(): callable
    {
        return $this->listDetermination->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new \RuntimeException("Unable to bind listDetermination");
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

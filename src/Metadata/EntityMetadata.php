<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\Http\HttpRepositoryInterface;
use Awwar\PhpHttpEntityManager\Proxy\EmptyProxy;
use ReflectionClass;
use ReflectionException;

class EntityMetadata
{
    private object $emptyInstance;

    private object $proxy;

    /**
     * @throws ReflectionException
     */
    public function __construct(
        string $entityClassName,
        private FieldsMetadata $fieldsMetadata,
        private ClientInterface $client,
        private ?string $name = null,
        string $proxyClassName = EmptyProxy::class,
        private bool $useDiffOnUpdate = true,
        private ?FilterMetadata $filterMetadata = null,
        private ?HttpRepositoryInterface $repository = null,
        private ?UrlMetadata $urlMetadata = null,
        private ?CallbacksMetadata $callbacksMetadata = null
    ) {
        if (empty($this->name)) {
            $path = explode('\\', $entityClassName);
            $this->name = strtolower(preg_replace("/([a-z])([A-Z])/", "$1_$2", array_pop($path)));
        }

        $this->proxy = (new ReflectionClass($proxyClassName))
            ->newInstanceWithoutConstructor();

        $this->emptyInstance = (new ReflectionClass($entityClassName))
            ->newInstanceWithoutConstructor();

        if ($this->filterMetadata === null) {
            $this->filterMetadata = new FilterMetadata();
        }

        if ($this->urlMetadata === null) {
            $this->urlMetadata = new UrlMetadata(
                one:    "/api/$name/{id}",
                list:   "/api/$name/",
                create: "/api/$name/",
                update: "/api/$name/{id}",
                delete: "/api/$name/{id}"
            );
        }

        if ($this->callbacksMetadata === null) {
            $this->callbacksMetadata = new CallbacksMetadata();
        }
    }

    public function getClassName(): string
    {
        return get_class($this->emptyInstance);
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getCreateLayout(): callable
    {
        return $this
            ->callbacksMetadata
            ->getCreateLayout()
            ->bindTo($this->emptyInstance, $this->emptyInstance);
    }

    public function getDefaultValue(string $property): mixed
    {
        $defaults = $this->fieldsMetadata->getDefaultValues();

        if (array_key_exists($property, $defaults) === false) {
            return null;
        }

        return $defaults[$property];
    }

    public function getEmptyInstance(): object
    {
        return clone $this->emptyInstance;
    }

    public function getFieldMap(string $name): array
    {
        return $this->dataFields[$name] ?? [];
    }

    public function getFilterOneQuery(): array
    {
        return $this->filterMetadata->getFilterOneQuery();
    }

    public function getFilterQuery(): array
    {
        return $this->filterMetadata->getFilterQuery();
    }

    public function getGetOneQuery(): array
    {
        return $this->filterMetadata->getGetOneQuery();
    }

    public function getIdProperty(): string
    {
        return $this->fieldsMetadata->getIdProperty();
    }

    public function getListDetermination(): callable
    {
        return $this
            ->callbacksMetadata
            ->getListDetermination()
            ->bindTo($this->emptyInstance, $this->emptyInstance);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProperties(): array
    {
        return $this->fieldsMetadata->getAllProperties();
    }

    public function getProxy(): object
    {
        return clone $this->proxy;
    }

    public function getProxyClass(): string
    {
        return get_class($this->proxy);
    }

    public function getRelationsMapper(): callable
    {
        return $this
            ->callbacksMetadata
            ->getRelationMapper()
            ->bindTo($this->emptyInstance, $this->emptyInstance);
    }

    /**
     * @return RelationMetadata[]
     */
    public function getRelationsMetadata(): array
    {
        return $this->fieldsMetadata->getRelationProperties();
    }

    public function getRepository(): ?HttpRepositoryInterface
    {
        return $this->repository;
    }

    public function getScalarProperties(): array
    {
        return $this->fieldsMetadata->getScalarProperties();
    }

    public function getUpdateLayout(): callable
    {
        return $this
            ->callbacksMetadata
            ->getUpdateLayout()
            ->bindTo($this->emptyInstance, $this->emptyInstance);
    }

    public function getUrlForCreate(): string
    {
        return $this->urlMetadata->getCreate();
    }

    public function getUrlForDelete(mixed $id = null): string
    {
        return str_replace('{id}', (string)$id, $this->urlMetadata->getDelete());
    }

    public function getUrlForList(): string
    {
        return $this->urlMetadata->getList();
    }

    public function getUrlForOne(mixed $id = null): string
    {
        return str_replace('{id}', (string)$id, $this->urlMetadata->getOne());
    }

    public function getUrlForUpdate(mixed $id = null): string
    {
        return str_replace('{id}', (string)$id, $this->urlMetadata->getUpdate());
    }

    public function isUseDiffOnUpdate(): bool
    {
        return $this->useDiffOnUpdate;
    }
}

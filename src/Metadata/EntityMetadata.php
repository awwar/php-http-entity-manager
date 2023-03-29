<?php

namespace Awwar\PhpHttpEntityManager\Metadata;

use Awwar\PhpHttpEntityManager\Client\ClientInterface;
use Awwar\PhpHttpEntityManager\Http\HttpRepositoryInterface;
use Awwar\PhpHttpEntityManager\Proxy\EmptyProxy;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class EntityMetadata
{
    private object $emptyInstance;

    private object $proxy;

    private FilterSettings $filterSettings;

    private UrlSettings $urlSettings;

    private CallbacksSettings $callbacksSettings;

    /**
     * @throws ReflectionException
     */
    public function __construct(
        string $entityClassName,
        private FieldsSettings $fieldsSettings,
        private ClientInterface $client,
        private string $name = '',
        string $proxyClassName = EmptyProxy::class,
        private bool $useDiffOnUpdate = true,
        ?FilterSettings $filterSettings = null,
        private ?HttpRepositoryInterface $repository = null,
        ?UrlSettings $urlSettings = null,
        ?CallbacksSettings $callbacksSettings = null
    ) {
        if (empty($this->name)) {
            $path = explode('\\', $entityClassName);
            $snakeCaseName = (string) preg_replace("/([a-z])([A-Z])/", "$1_$2", array_pop($path));
            $this->name = strtolower($snakeCaseName);
        }

        $this->proxy = (new ReflectionClass($proxyClassName))
            ->newInstanceWithoutConstructor();

        $this->emptyInstance = (new ReflectionClass($entityClassName))
            ->newInstanceWithoutConstructor();

        if ($filterSettings === null) {
            $this->filterSettings = new FilterSettings();
        }

        if ($urlSettings === null) {
            $this->urlSettings = new UrlSettings(
                one: "/api/$this->name/{id}/",
                list: "/api/$this->name/",
                create: "/api/$this->name/",
                update: "/api/$this->name/{id}/",
                delete: "/api/$this->name/{id}/"
            );
        }

        if ($callbacksSettings === null) {
            $callbacksSettings = new CallbacksSettings();
        }

        $this->callbacksSettings = $callbacksSettings;
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
                ->callbacksSettings
                ->getCreateLayout()
                ->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException('Unable to binding callback');
    }

    public function getDefaultValue(string $property): mixed
    {
        $defaults = $this->fieldsSettings->getDefaultValues();

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
        return $this->fieldsSettings->getDataFieldsSettings()[$name] ?? [];
    }

    public function getFilterOneQuery(): array
    {
        return $this->filterSettings->getFilterOneQuery();
    }

    public function getFilterQuery(): array
    {
        return $this->filterSettings->getFilterQuery();
    }

    public function getGetOneQuery(): array
    {
        return $this->filterSettings->getGetOneQuery();
    }

    public function getIdProperty(): string
    {
        return $this->fieldsSettings->getIdProperty();
    }

    public function getListDetermination(): callable
    {
        return $this
                ->callbacksSettings
                ->getListDetermination()
                ->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException('Unable to binding callback');
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getProperties(): array
    {
        return $this->fieldsSettings->getAllProperties();
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
                ->callbacksSettings
                ->getRelationMapper()
                ->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException('Unable to binding callback');
    }

    /**
     * @return RelationSettings[]
     */
    public function getRelationsMetadata(): array
    {
        return $this->fieldsSettings->getRelationProperties();
    }

    public function getRepository(): ?HttpRepositoryInterface
    {
        return $this->repository;
    }

    public function getScalarProperties(): array
    {
        return $this->fieldsSettings->getScalarProperties();
    }

    public function getUpdateLayout(): callable
    {
        return $this
                ->callbacksSettings
                ->getUpdateLayout()
                ->bindTo($this->emptyInstance, $this->emptyInstance)
            ?? throw new RuntimeException('Unable to binding callback');
    }

    public function getUrlForCreate(): string
    {
        return $this->urlSettings->getCreate();
    }

    public function getUrlForDelete(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlSettings->getDelete());
    }

    public function getUrlForList(): string
    {
        return $this->urlSettings->getList();
    }

    public function getUrlForOne(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlSettings->getOne());
    }

    public function getUrlForUpdate(mixed $id = null): string
    {
        return str_replace('{id}', (string) $id, $this->urlSettings->getUpdate());
    }

    public function isUseDiffOnUpdate(): bool
    {
        return $this->useDiffOnUpdate;
    }
}

<?php

namespace Awwar\PhpHttpEntityManager\UnitOfWork;

use Awwar\PhpHttpEntityManager\Collection\GeneralCollection;
use Awwar\PhpHttpEntityManager\DataStructure\SmartMap;
use Awwar\PhpHttpEntityManager\Metadata\EntityMetadata;
use Awwar\PhpHttpEntityManager\Metadata\RelationSettings;
use Closure;
use Exception;
use RuntimeException;
use Throwable;

class SuitedUpEntity
{
    private bool $isDeleted = false;

    private array|null $copy = null;

    private function __construct(
        private object $original,
        private EntityMetadata $entityMetadata
    ) {
    }

    public static function create(object $original, EntityMetadata $entityMetadata): self
    {
        return new self($original, $entityMetadata);
    }

    public function callAfterCreate(array $data): void
    {
        $map = $this->entityMetadata->getFieldMap('afterCreate');

        $this->mapScalarData($map, $data);
    }

    public function callAfterRead(array $data, EntityCreatorInterface $creator): void
    {
        $map = $this->entityMetadata->getFieldMap('afterRead');

        $this->mapScalarData($map, $data);

        $this->mapNestedRelation($data, $creator);
    }

    public function callAfterUpdate(array $data): void
    {
        $map = $this->entityMetadata->getFieldMap('afterUpdate');

        $this->mapScalarData($map, $data);
    }

    public function callBeforeCreate(): array
    {
        $map = $this->entityMetadata->getFieldMap('beforeCreate');

        $changesDTO = new EntityChangesDTO(
            entityChanges: [],
            relationChanges: [],
            entitySnapshot: $this->getScalarSnapshot(),
            relationsSnapshot: $this->getRelationValues()
        );

        $layout = $this->entityMetadata->getCreateRequestLayoutCallback($this->original)($changesDTO);

        $smartMap = new SmartMap($layout);

        foreach ($map as $field => $path) {
            if (
                $field === $this->getMetadata()->getIdProperty()
                || $this->issetProperty($this->original, $field) === false
            ) {
                continue;
            }

            $smartMap->put($path, $this->getValue($this->original, $field));
        }

        return $smartMap->toArray();
    }

    public function callBeforeUpdate(
        array $entityChanges,
        array $relationChanges,
    ): array {
        $map = $this->entityMetadata->getFieldMap('beforeUpdate');

        $changesDTO = new EntityChangesDTO(
            entityChanges: $entityChanges,
            relationChanges: $relationChanges,
            entitySnapshot: $this->getScalarSnapshot(),
            relationsSnapshot: $this->getRelationValues()
        );

        $layout = $this->entityMetadata->getUpdateRequestLayoutCallback($this->original)($changesDTO);

        $smartMap = new SmartMap($layout);

        if ($this->entityMetadata->isUseDiffOnUpdate()) {
            foreach ($entityChanges as $field => $value) {
                $path = $map[$field] ?? null;
                if ($path === null) {
                    continue;
                }
                $smartMap->put($path, $value);
            }
        } else {
            foreach ($map as $field => $path) {
                if ($this->issetProperty($this->original, $field) === false) {
                    continue;
                }

                $smartMap->put($path, $this->getValue($this->original, $field));
            }
        }

        return $smartMap->toArray();
    }

    public function delete(): void
    {
        $this->isDeleted = true;
    }

    public function getClass(): string
    {
        return get_class($this->original);
    }

    public function getId(): ?string
    {
        return $this->getEntityId($this->original);
    }

    public function getMetadata(): EntityMetadata
    {
        return $this->entityMetadata;
    }

    public function getOriginal(): object
    {
        return $this->original;
    }

    public function getRelationChanges(): array
    {
        if ($this->copy === null) {
            throw new Exception("Got suit without copy!");
        }

        $relations = $this->entityMetadata->getRelationsMetadata();

        $changes = [];

        foreach ($relations as $property => $mapping) {
            $copy = $this->copy['relations'][$property] ?? $mapping->getDefault();
            $original = $this->getRelationValue($this->original, $property, $mapping);

            $changes [$mapping->getName()] = [
                'original' => $original,
                'copy'     => $copy,
                'iterable' => $mapping->isCollection(),
            ];
        }

        $relationChanges = [];
        $relationDeleted = [];
        foreach ($changes as $name => $value) {
            $isIterable = $value['iterable'];
            $original = $value['original'];
            $copy = $value['copy'];
            if ($isIterable && is_iterable($original)) {
                //ToDo: тут только на добавление, нужно добавить на удаление
                foreach ($original as $originalEntity) {
                    $key = $this->getEntityIsNew($originalEntity)
                        ? $this->getEntitySplId($originalEntity)
                        : $this->getEntityUniqueId($originalEntity);
                    if (false === in_array($key, $copy)) {
                        // added
                        $relationChanges[$name][$key] = $originalEntity;
                    }
                }
            } else {
                if ($original !== null) {
                    $key = $this->getEntityUniqueId($original);

                    if ($key === $copy) {
                        continue;
                    }

                    $relationChanges[$name] = $original;
                } elseif ($copy !== null) {
                    $relationDeleted[$name] = true;
                }
            }
        }

        return $relationChanges;
    }

    public function getRelationSnapshot(): array
    {
        $snapshot = [];

        $relations = $this->entityMetadata->getRelationsMetadata();

        foreach ($relations as $property => $mapping) {
            if (false === $this->issetProperty($this->original, $property)) {
                $snapshot[$property] = $mapping->getDefault();

                continue;
            }
            $relation = $this->getRelationValue($this->original, $property, $mapping);

            if (is_iterable($relation) && $mapping->isCollection()) {
                $value = [];
                foreach ($relation as $subRelation) {
                    $value[] = $this->getEntityUniqueId($subRelation);
                }
            } else {
                $value = $relation === null ? null : $this->getEntityUniqueId($relation);
            }

            $snapshot[$property] = $value;
        }

        return $snapshot;
    }

    public function getRelationValues(): array
    {
        $relations = $this->entityMetadata->getRelationsMetadata();

        $snapshot = [];

        foreach ($relations as $property => $mapping) {
            $snapshot[$mapping->getName()] = $this->getRelationValue($this->original, $property, $mapping);
        }

        return $snapshot;
    }

    public function getSPLId(): string
    {
        return $this->getEntitySplId($this->original);
    }

    public function getScalarChanges(): array
    {
        if ($this->copy === null) {
            throw new Exception("Got suit without copy!");
        }

        $actual = $this->getScalarSnapshot();
        $copy = $this->copy['properties'];

        $properties = $this->entityMetadata->getScalarProperties();

        $changes = [];

        foreach ($properties as $property) {
            // ToDo: покрыть тестами возможные кейсы
            $actualValue = $actual[$property] ?? null;
            $copyValue = $copy[$property] ?? null;

            if ($actualValue !== $copyValue) {
                $changes[$property] = $actualValue;
            }
        }

        return $changes;
    }

    public function getScalarSnapshot(): array
    {
        $properties = $this->entityMetadata->getScalarProperties();

        $snapshot = [];

        foreach ($properties as $property) {
            if ($property === $this->entityMetadata->getIdProperty()) {
                continue;
            }

            $snapshot[$property] = $this->getValue($this->original, $property);
        }

        return $snapshot;
    }

    public function getUniqueId(): string
    {
        return $this->getEntityUniqueId($this->original);
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function isNew(): bool
    {
        return $this->getEntityIsNew($this->original);
    }

    public function isProxy(): bool
    {
        return $this->getClass() === $this->getMetadata()->getProxyClass();
    }

    public function isProxyInitialized(): bool
    {
        if (!$this->isProxy()) {
            return true;
        }

        return $this->getValue($this->original, '__initialized');
    }

    public function markProxyAsInitialized(): void
    {
        if (!$this->isProxy()) {
            throw new RuntimeException('This suit is not a proxy!');
        }

        $this->setValue($this->original, '__initialized', true);
    }

    public function proxy(Closure $managerCallback, mixed $id = null): void
    {
        $this->original = $this->entityMetadata->getProxy();

        $callback = function ($idProperty, $properties) use ($managerCallback, $id) {
            //@phpstan-ignore-next-line
            $this->__prepare($idProperty, $properties, $managerCallback, $id === null);
        };

        $metadata = $this->entityMetadata;

        $callback->call($this->original, $metadata->getIdProperty(), $metadata->getProperties());

        if ($id !== null) {
            $this->setId($id);
        }
    }

    public function setId(mixed $id): void
    {
        $property = $this->entityMetadata->getIdProperty();

        $this->setValue($this->original, $property, $id);
    }

    public function setIdAfterRead(array $data): void
    {
        $map = $this->entityMetadata->getFieldMap('afterRead');
        $idProperty = $this->entityMetadata->getIdProperty();
        $smartMap = new SmartMap($data);

        $this->setId($this->getByDot($smartMap, $map[$idProperty], $idProperty));
    }

    //public function isLateProxy(): bool
    //{
    //    if (!$this->isProxy()) {
    //        return false;
    //    }
    //
    //    return $this->getValue($this->original, '__late_proxy');
    //}

    public function startWatch(): void
    {
        $this->copy = [
            'properties' => $this->getScalarSnapshot(),
            'relations'  => $this->getRelationSnapshot(),
        ];
    }

    private function createNestedRelation(
        RelationSettings $mapping,
        iterable $relationIterator,
        EntityCreatorInterface $creator
    ): ?object {
        $result = [];

        foreach ($relationIterator as $dataContainer) {
            $result[] = $creator->createEntityWithData($mapping->getClass(), $dataContainer);

            if ($mapping->isCollection() === false) {
                break;
            }
        }

        $result = array_filter($result);

        return $mapping->isCollection() ? new GeneralCollection($result) : array_pop($result);
    }

    private function getByDot(SmartMap $smartMap, string $path, string $field): mixed
    {
        $default = $this->getMetadata()->getDefaultValue($field);

        return $smartMap->get($path, $default);
    }

    private function getEntityId(object $entity): ?string
    {
        $property = $this->entityMetadata->getIdProperty();

        try {
            return $this->getValue($entity, $property);
        } catch (Throwable) {
            return null;
        }
    }

    private function getEntityIsNew(object $entity): bool
    {
        //if ($this->isLateProxy()) {
        //    return true;
        //}

        return $this->getEntityId($entity) === null;
    }

    private function getEntitySplId(object $entity): string
    {
        return (string) spl_object_id($entity);
    }

    private function getEntityUniqueId(object $entity): string
    {
        if ($this->getEntityIsNew($entity)) {
            throw new Exception("Unable to get uniqueId when entity is new!");
        }

        return sha1($this->getEntityId($entity) . $this->entityMetadata->getClassName());
    }

    private function getRelationValue(object $object, string $property, RelationSettings $mapping): mixed
    {
        $data = $this->getValue($object, $property);

        return $data ?? $mapping->getDefault();
    }

    private function getValue(object $object, string $property): mixed
    {
        $setter = function () use ($property) {
            return $this->{$property};
        };

        return $setter->call($object);
    }

    private function issetProperty(object $object, string $property): bool
    {
        $setter = function () use ($property) {
            return property_exists($this, $property);
        };

        return $setter->call($object);
    }

    private function mapNestedRelation(array $data, EntityCreatorInterface $creator): void
    {
        $relationsMapper = $this->entityMetadata->getRelationsMapper($this->original);
        $relations = $this->entityMetadata->getRelationsMetadata();

        foreach ($relations as $field => $mapping) {
            $mappedData = call_user_func($relationsMapper, $data, $mapping->getName());

            $value = $this->createNestedRelation($mapping, $mappedData, $creator);

            $this->setValue($this->original, $field, $value);
        }
    }

    private function mapScalarData(array $map, array $data): void
    {
        $smartMap = new SmartMap($data);

        foreach ($map as $field => $path) {
            $this->setValue($this->original, $field, $this->getByDot($smartMap, $path, $field));
        }
    }

    private function setValue(object $object, string $property, mixed $value): void
    {
        $setter = function ($value) use ($property) {
            $this->{$property} = $value;
        };

        $setter->call($object, $value);
    }
}

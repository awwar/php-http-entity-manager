<?php

namespace Awwar\PhpHttpEntityManager\Http;

use Awwar\PhpHttpEntityManager\Exception\NotFoundException;
use Awwar\PhpHttpEntityManager\Http\ListIterator\Data;
use Awwar\PhpHttpEntityManager\Http\Resource\FullData;
use Awwar\PhpHttpEntityManager\Http\Resource\NoData;
use Awwar\PhpHttpEntityManager\Http\Resource\Reference;
use Awwar\PhpHttpEntityManager\UOW\EntityAtelier;
use Awwar\PhpHttpEntityManager\UOW\HttpUnitOfWorkInterface;
use Generator;
use LogicException;
use RuntimeException;

class HttpEntityManager implements HttpEntityManagerInterface, EntityCreatorInterface
{
    public function __construct(
        private HttpUnitOfWorkInterface $unitOfWork,
        private EntityAtelier $entityAtelier
    ) {
    }

    public function clear(string $objectName = null): void
    {
        $this->unitOfWork->clear($objectName);
    }

    public function contains(object $object): bool
    {
        $suit = $this->entityAtelier->suitUpEntity($object);

        return $this->unitOfWork->hasSuit($suit);
    }

    public function createEntityWithData(string $className, mixed $data): ?object
    {
        #             createEntityWithData
        #              /                \
        #          FullData         Reference
        #             |                  |
        #      exist in IDMap?     exist in IDMap?
        #         /        |           |       \
        #       yes        no         yes      no
        #        |         |           |       |
        #        |     create and      |     proxy +
        #        |     fill + add       |   add to IdMap
        #        |     to IdMap        |
        #    is proxy?              just return
        #    /      \
        #   yes     no
        #    |       |
        # fill and    |
        # return     |
        #        just return
        if ($data instanceof NoData) {
            return null;
        }

        $suit = $this->entityAtelier->suitUpClass($className);

        if ($data instanceof FullData) {
            $suit->setIdAfterRead($data->getData());

            if ($this->unitOfWork->hasSuit($suit)) {
                $suitFromIdentity = $this->unitOfWork->getFromIdentity($suit);

                if ($suitFromIdentity->isProxy()) {
                    $suitFromIdentity->markProxyAsInitialized();
                    $suitFromIdentity->callAfterRead($data->getData(), $this);
                }
            } else {
                $suit->callAfterRead($data->getData(), $this);
                $this->unitOfWork->commit($suit, false);
            }
        } elseif ($data instanceof Reference) {
            $suit->setId($data->getId());

            if (false === $this->unitOfWork->hasSuit($suit)) {
                $suit->proxy(fn ($obj) => $this->refresh($obj), $data->getId());
                $this->unitOfWork->commit($suit, false);
            }
        } else {
            throw new LogicException("Unable to map relation - invalid data type!");
        }

        return $this->unitOfWork->getFromIdentity($suit)->getOriginal();
    }

    public function detach(object $object): void
    {
        $suit = $this->entityAtelier->suitUpEntity($object);

        $this->unitOfWork->remove($suit);
    }

    public function find(string $className, mixed $id, array $criteria = []): object
    {
        $suit = $this->entityAtelier->suitUpClass($className);
        $suit->setId($id);

        if (false === $this->unitOfWork->hasSuit($suit)) {
            $metadata = $suit->getMetadata();

            $newCriteria = array_merge($metadata->getGetOneQuery(), $criteria);

            $data = $metadata->getClient()->get($metadata->getUrlForOne($id), $newCriteria);

            $suit->callAfterRead($data, $this);

            $this->unitOfWork->commit($suit);
        }

        return $this->unitOfWork->getFromIdentity($suit)->getOriginal();
    }

    public function flush(): void
    {
        $this->unitOfWork->flush();
    }

    public function getRepository(string $className): HttpRepositoryInterface
    {
        $suit = $this->entityAtelier->suitUpClass($className);

        return $suit->getMetadata()->getRepository() ?? new HttpRepository($this, $className);
    }

    public function iterate(
        string $className,
        array $criteria,
        ?string $url = null,
        bool $isFilterOne = false
    ): Generator {
        $suit = $this->entityAtelier->suitUpClass($className);
        $metadata = $suit->getMetadata();

        $criteria = array_merge($isFilterOne ? $metadata->getFilterOneQuery() : $metadata->getFilterQuery(), $criteria);

        $data = $metadata->getClient()->get($url === null ? $metadata->getUrlForList() : $url, $criteria);

        $iterator = $metadata->getListDetermination()($data);

        $nextUrl = null;

        $firstIteration = true;

        do {
            $signal = $iterator->current();

            if ($signal instanceof Data) {
                $nextUrl = $signal->getUrl();
            }

            if ($signal === null) {
                if ($nextUrl === null) {
                    if ($firstIteration === true && $isFilterOne === true) {
                        throw new NotFoundException(entity: $metadata->getName());
                    }
                    break;
                }
                $data = $metadata->getClient()->get($nextUrl, $criteria);

                $iterator = $metadata->getListDetermination()($data);
                $nextUrl = null;
                continue;
            }
            $firstIteration = false;

            $newSuit = $this->entityAtelier->suitUpClass($className);
            $newSuit->callAfterRead($signal->getData(), $this);

            $this->unitOfWork->commit($newSuit);

            yield $newSuit->getOriginal();

            $iterator->next();
        } while (true);
    }

    public function merge(object $object): void
    {
        throw new RuntimeException('Merge is not implemented!');
    }

    public function persist(object $object): void
    {
        $suit = $this->entityAtelier->suitUpEntity($object);

        $this->unitOfWork->commit($suit);
    }

    public function refresh(object $object): void
    {
        $suit = $this->entityAtelier->suitUpEntity($object);

        $suit = $this->unitOfWork->getFromIdentity($suit);
        $metadata = $suit->getMetadata();

        $data = $metadata->getClient()->get(
            $metadata->getUrlForOne($suit->getId()),
            $metadata->getGetOneQuery()
        );

        $suit->callAfterRead($data, $this);
    }

    public function remove(object $object): void
    {
        $suit = $this->entityAtelier->suitUpEntity($object);

        $this->unitOfWork->delete($suit);
    }
}

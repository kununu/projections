<?php
declare(strict_types=1);

namespace Kununu\Projections\Repository;

use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Tag\Tags;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractProjectionRepository implements ProjectionRepositoryInterface
{
    public function __construct(
        protected readonly CacheItemPoolInterface $cachePool,
        protected readonly CacheSerializerInterface $serializer
    ) {
    }

    public function add(ProjectionItemInterface $item): void
    {
        $cacheItem = $this->createCacheItem($item);

        if (!$this->cachePool->save($cacheItem)) {
            throw new ProjectionException('Not possible to add projection item on cache pool');
        }
    }

    public function addDeferred(ProjectionItemInterface $item): void
    {
        $cacheItem = $this->createCacheItem($item);

        if (!$this->cachePool->saveDeferred($cacheItem)) {
            throw new ProjectionException('Not possible to save deferred projection item on cache pool');
        }
    }

    public function flush(): void
    {
        if (!$this->cachePool->commit()) {
            throw new ProjectionException('Not possible to add projection items on cache pool by flush');
        }
    }

    public function get(ProjectionItemInterface $item): ?ProjectionItemInterface
    {
        $cacheItem = $this->cachePool->getItem($item->getKey());

        if ($cacheItem->isHit()) {
            return $this->serializer->deserialize($cacheItem->get(), $item::class);
        }

        return null;
    }

    public function delete(ProjectionItemInterface $item): void
    {
        if (!$this->cachePool->deleteItem($item->getKey())) {
            throw new ProjectionException('Not possible to delete projection item on cache pool');
        }
    }

    abstract public function deleteByTags(Tags $tags): void;

    protected function createCacheItem(ProjectionItemInterface $item): CacheItemInterface
    {
        $cacheItem = $this->cachePool->getItem($item->getKey());
        $cacheItem->set($this->serializer->serialize($item));

        return $cacheItem;
    }
}

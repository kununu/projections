<?php
declare(strict_types=1);

namespace Kununu\Projections\Repository;

use JMS\Serializer\SerializerInterface;
use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\Tags;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class CachePoolProjectionRepository implements ProjectionRepositoryInterface
{
    private const JMS_SERIALIZER_FORMAT = 'json';

    private $cachePool;
    private $serializer;

    public function __construct(TagAwareAdapterInterface $cachePool, SerializerInterface $serializer)
    {
        $this->cachePool = $cachePool;
        $this->serializer = $serializer;
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

        if (!$cacheItem->isHit()) {
            return null;
        }

        return $this->serializer->deserialize($cacheItem->get(), get_class($item), self::JMS_SERIALIZER_FORMAT);
    }

    public function delete(ProjectionItemInterface $item): void
    {
        if (!$this->cachePool->deleteItem($item->getKey())) {
            throw new ProjectionException('Not possible to delete projection item on cache pool');
        }
    }

    public function deleteByTags(Tags $tags): void
    {
        if (!$this->cachePool->invalidateTags($tags->raw())) {
            throw new ProjectionException('Not possible to delete projection items on cache pool based on tag');
        }
    }

    private function createCacheItem(ProjectionItemInterface $item): CacheItemInterface
    {
        $cacheItem = $this->cachePool->getItem($item->getKey());
        $cacheItem->set($this->serializer->serialize($item, self::JMS_SERIALIZER_FORMAT));
        $cacheItem->tag($item->getTags()->raw());

        return $cacheItem;
    }
}

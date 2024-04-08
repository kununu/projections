<?php
declare(strict_types=1);

namespace Kununu\Projections\Repository;

use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Tag\Tags;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class SymfonyCacheProjectionRepository extends AbstractProjectionRepository
{
    public function __construct(TagAwareAdapterInterface $cachePool, CacheSerializerInterface $serializer)
    {
        parent::__construct($cachePool, $serializer);
    }

    public function deleteByTags(Tags $tags): void
    {
        if (!$this->getCachePool()->invalidateTags($tags->raw())) {
            throw new ProjectionException('Not possible to delete projection items on cache pool based on tag');
        }
    }

    protected function createCacheItem(ProjectionItemInterface $item): CacheItemInterface
    {
        $cacheItem = parent::createCacheItem($item);
        $cacheItem->tag($item->getTags()->raw());

        return $cacheItem;
    }

    private function getCachePool(): TagAwareAdapterInterface
    {
        assert($this->cachePool instanceof TagAwareAdapterInterface);

        return $this->cachePool;
    }
}

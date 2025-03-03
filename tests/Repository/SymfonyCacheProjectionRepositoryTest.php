<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use Closure;
use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Repository\SymfonyCacheProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;

final class SymfonyCacheProjectionRepositoryTest extends AbstractProjectionRepositoryTestCase
{
    public function testDeleteByTags(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['tag_1', 'tag_2'])
            ->willReturn(true);

        $this->projectionRepository->deleteByTags(new Tags(new Tag('tag_1'), new Tag('tag_2')));
    }

    public function testWhenDeleteByTagsFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection items on cache pool based on tag');

        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturn(false);

        $this->projectionRepository->deleteByTags(new Tags());
    }

    protected static function extraAssertionsForAdd(CacheItemInterface $cacheItem): void
    {
        self::assertTags($cacheItem);
    }

    protected static function extraAssertionsForAddIterable(CacheItemInterface $cacheItem): void
    {
        self::assertTags($cacheItem);
    }

    protected static function extraAssertionsForAddDeferred(CacheItemInterface $cacheItem): void
    {
        self::assertTags($cacheItem);
    }

    protected function getCachePool(): MockObject&CacheItemPoolInterface
    {
        if (null === $this->cachePool) {
            $this->cachePool = $this->createMock(TagAwareAdapterInterface::class);
        }

        return $this->cachePool;
    }

    protected function getProjectionRepository(): ProjectionRepositoryInterface
    {
        $cachePool = $this->getCachePool();

        self::assertInstanceOf(TagAwareAdapterInterface::class, $cachePool);

        return new SymfonyCacheProjectionRepository($cachePool, $this->serializer);
    }

    /**
     * In symfony/cache 6.4, AdapterInterface is defined to return a concrete implementation of CacheItemInterface,
     * so we need an adapter from to create a Symfony CacheItem instance from CacheItemStub.
     */
    protected function adaptCacheItem(CacheItemInterface $cacheItem): CacheItem
    {
        // Since the properties are protected we need to bind it to this closure to be able to set them.
        $item = Closure::bind(
            static function(string $key, mixed $value, bool $isHit): CacheItem {
                $item = new CacheItem();
                $item->key = $key;
                $item->value = $value;
                $item->isHit = $isHit;
                $item->isTaggable = true;

                return $item;
            },
            null,
            CacheItem::class
        );

        return $item($cacheItem->getKey(), $cacheItem->get(), $cacheItem->isHit());
    }

    private static function assertTags(CacheItemInterface $cacheItem): void
    {
        self::assertInstanceOf(CacheItem::class, $cacheItem);

        // CacheItem::getMetadata returns the metadata (where the tags are stored).
        //
        // Since the values are only updated when the cache adapter commits, we need to get the "newMetadata"
        // property. That is protected, so let's bypass protection rules and get the property value
        $tags = (Closure::bind(fn(): array => array_keys($this->newMetadata['tags'] ?? []), $cacheItem, $cacheItem))();

        self::assertEquals(['test', 'kununu', 'id_item'], $tags);
    }
}

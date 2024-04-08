<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Tests\Stubs\CacheItem\CacheItemStub;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractProjectionRepositoryTestCase extends TestCase
{
    private const ID = 'id_item';
    private const KEY = 'test_id_item';
    private const SERIALIZED = 'serialized_projection_item_dummy';

    protected MockObject|CacheItemPoolInterface|null $cachePool = null;
    protected MockObject|CacheSerializerInterface $serializer;

    public function testAdd(): void
    {
        $cacheItem = $this->adaptCacheItem(new CacheItemStub(self::ID));
        $item = new ProjectionItemStub(self::ID);

        $this->getCachePool()
            ->expects(self::once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($cacheItem);

        $this->getCachePool()
            ->expects(self::once())
            ->method('save')
            ->willReturn(true);

        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->add($item);

        self::assertEquals(self::SERIALIZED, $cacheItem->get());
        $this->extraAssertionsForAdd($cacheItem);
    }

    public function testAddIterable(): void
    {
        $cacheItem = $this->adaptCacheItem(new CacheItemStub(self::ID));
        $item = (new ProjectionItemIterableStub(self::ID, 'itn'))->storeData(['id' => 'beiga', 'value' => 1000]);

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with('test_iterable_id_item')
            ->willReturn($cacheItem);

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->willReturn(true);

        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($item)
            ->willReturnCallback(fn(ProjectionItemIterableStub $item): string => json_encode([
                'key'   => $item->getKey(),
                'stuff' => $item->stuff,
                'data'  => $item->data(),
            ]));

        $this->getProjectionRepository()->add($item);

        self::assertEquals(
            '{"key":"test_iterable_id_item","stuff":"itn","data":{"id":"beiga","value":1000}}',
            $cacheItem->get()
        );
        $this->extraAssertionsForAddIterable($cacheItem);
    }

    public function testAddDeferred(): void
    {
        $cacheItem = $this->adaptCacheItem(new CacheItemStub(self::ID));
        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($cacheItem);

        $this->cachePool
            ->expects(self::never())
            ->method('save');

        $this->cachePool
            ->expects(self::once())
            ->method('saveDeferred')
            ->willReturn(true);

        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->addDeferred($item);

        self::assertEquals(self::SERIALIZED, $cacheItem->get());
        $this->extraAssertionsForAddDeferred($cacheItem);
    }

    public function testWhenAddFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection item on cache pool');

        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($this->adaptCacheItem(new CacheItemStub(self::KEY)));

        $this->cachePool
            ->expects(self::once())
            ->method('save')
            ->willReturn(false);

        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->add($item);
    }

    public function testWhenAddDeferredFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to save deferred projection item on cache pool');

        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($this->adaptCacheItem(new CacheItemStub(self::KEY)));

        $this->cachePool
            ->expects(self::never())
            ->method('save');

        $this->cachePool
            ->expects(self::once())
            ->method('saveDeferred')
            ->willReturn(false);

        $this->serializer
            ->expects(self::once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->addDeferred($item);
    }

    public function testGetExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub(self::ID);
        $projectionItemOnCache = new ProjectionItemStub(self::ID);
        $cacheItem = $this->adaptCacheItem((new CacheItemStub(self::ID))->setHit()->set(self::SERIALIZED));

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(self::SERIALIZED, $projectionItem::class)
            ->willReturn($projectionItemOnCache);

        self::assertEquals($projectionItemOnCache, $this->getProjectionRepository()->get($projectionItem));
    }

    public function testGetNonExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub(self::ID);
        $cacheItem = $this->adaptCacheItem((new CacheItemStub(self::ID))->setNotHit());

        $this->cachePool
            ->expects(self::once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects(self::never())
            ->method('deserialize');

        self::assertNull($this->getProjectionRepository()->get($projectionItem));
    }

    public function testDelete(): void
    {
        $this->getCachePool()
            ->expects(self::once())
            ->method('deleteItem')
            ->willReturn(true);

        $this->getProjectionRepository()->delete(new ProjectionItemStub(self::ID));
    }

    public function testWhenDeleteFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection item on cache pool');

        $this->cachePool
            ->expects(self::once())
            ->method('deleteItem')
            ->willReturn(false);

        $this->getProjectionRepository()->delete(new ProjectionItemStub(self::ID));
    }

    public function testFlush(): void
    {
        $this->getCachePool()
            ->expects(self::once())
            ->method('commit')
            ->willReturn(true);

        $this->getProjectionRepository()->flush();
    }

    public function testWhenFlushFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection items on cache pool by flush');

        $this->getCachePool()
            ->expects(self::once())
            ->method('commit')
            ->willReturn(false);

        $this->getProjectionRepository()->flush();
    }

    abstract protected function getCachePool(): MockObject|CacheItemPoolInterface;

    abstract protected function getProjectionRepository(): ProjectionRepositoryInterface;

    protected function setUp(): void
    {
        $this->cachePool = $this->getCachePool();
        $this->serializer = $this->createMock(CacheSerializerInterface::class);
    }

    protected function extraAssertionsForAdd(CacheItemInterface $cacheItem): void
    {
    }

    protected function extraAssertionsForAddIterable(CacheItemInterface $cacheItem): void
    {
    }

    protected function extraAssertionsForAddDeferred(CacheItemInterface $cacheItem): void
    {
    }

    protected function adaptCacheItem(CacheItemInterface $cacheItem): CacheItemInterface
    {
        return $cacheItem;
    }
}

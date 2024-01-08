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
use Psr\Cache\CacheItemPoolInterface;

abstract class AbstractProjectionRepositoryTestCase extends TestCase
{
    protected const ID = 'id_item';
    protected const KEY = 'test_id_item';
    protected const SERIALIZED = 'serialized_projection_item_dummy';

    protected MockObject|CacheItemPoolInterface|null $cachePool = null;
    protected MockObject|CacheSerializerInterface $serializer;

    public function testAdd(): void
    {
        $cacheItemStub = new CacheItemStub(self::ID);
        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($cacheItemStub);

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->add($item);

        $this->assertEquals(self::SERIALIZED, $cacheItemStub->get());
        $this->extraAssertionsForAdd($cacheItemStub);
    }

    public function testAddIterable(): void
    {
        $cacheItemStub = new CacheItemStub(self::ID);
        $item = (new ProjectionItemIterableStub(self::ID, 'itn'))->storeData(['id' => 'beiga', 'value' => 1000]);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_iterable_id_item')
            ->willReturn($cacheItemStub);

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturnCallback(fn(ProjectionItemIterableStub $item): string => json_encode([
                'key'   => $item->getKey(),
                'stuff' => $item->stuff(),
                'data'  => $item->data(),
            ]));

        $this->getProjectionRepository()->add($item);

        $this->assertEquals(
            '{"key":"test_iterable_id_item","stuff":"itn","data":{"id":"beiga","value":1000}}',
            $cacheItemStub->get()
        );
        $this->extraAssertionsForAddIterable($cacheItemStub);
    }

    public function testWhenAddFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection item on cache pool');

        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn(new CacheItemStub(self::KEY));

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->add($item);
    }

    public function testAddDeferred(): void
    {
        $cacheItemStub = new CacheItemStub(self::ID);
        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn($cacheItemStub);

        $this->cachePool
            ->expects($this->never())
            ->method('save');

        $this->cachePool
            ->expects($this->once())
            ->method('saveDeferred')
            ->willReturn(true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->addDeferred($item);

        $this->assertEquals(self::SERIALIZED, $cacheItemStub->get());
        $this->extraAssertionsForAddDeferred($cacheItemStub);
    }

    public function testWhenAddDeferredFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to save deferred projection item on cache pool');

        $item = new ProjectionItemStub(self::ID);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with(self::KEY)
            ->willReturn(new CacheItemStub(self::KEY));

        $this->cachePool
            ->expects($this->never())
            ->method('save');

        $this->cachePool
            ->expects($this->once())
            ->method('saveDeferred')
            ->willReturn(false);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn(self::SERIALIZED);

        $this->getProjectionRepository()->addDeferred($item);
    }

    public function testGetExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub(self::ID);
        $projectionItemOnCache = new ProjectionItemStub(self::ID);
        $cacheItem = (new CacheItemStub(self::ID))->setHit()->set(self::SERIALIZED);

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with(self::SERIALIZED, $projectionItem::class)
            ->willReturn($projectionItemOnCache);

        $this->assertEquals($projectionItemOnCache, $this->getProjectionRepository()->get($projectionItem));
    }

    public function testGetNonExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub(self::ID);
        $cacheItem = (new CacheItemStub(self::ID))->setNotHit();

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects($this->never())
            ->method('deserialize');

        $this->assertNull($this->getProjectionRepository()->get($projectionItem));
    }

    public function testDelete(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willReturn(true);

        $this->getProjectionRepository()->delete(new ProjectionItemStub(self::ID));
    }

    public function testWhenDeleteFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection item on cache pool');

        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willReturn(false);

        $this->getProjectionRepository()->delete(new ProjectionItemStub(self::ID));
    }

    public function testFlush(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $this->getProjectionRepository()->flush();
    }

    public function testWhenFlushFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection items on cache pool by flush');

        $this->cachePool
            ->expects($this->once())
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

    protected function extraAssertionsForAdd(CacheItemStub $cacheItemStub): void
    {
    }

    protected function extraAssertionsForAddIterable(CacheItemStub $cacheItemStub): void
    {
    }

    protected function extraAssertionsForAddDeferred(CacheItemStub $cacheItemStub): void
    {
    }
}

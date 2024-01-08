<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\Repository\CachePoolProjectionRepository;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use Kununu\Projections\Tests\Stubs\CacheItem\CacheItemStub;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class CachePoolProjectionRepositoryTest extends TestCase
{
    private MockObject|TagAwareAdapterInterface $cachePool;
    private MockObject|CacheSerializerInterface $serializer;

    public function testAdd(): void
    {
        $cacheItemStub = new CacheItemStub('id_item');

        $item = new ProjectionItemStub('id_item');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
            ->willReturn($cacheItemStub);

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->add($item);

        $this->assertEquals('serialized_projection_item_dummy', $cacheItemStub->get());
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }

    public function testAddIterable(): void
    {
        $cacheItemStub = new CacheItemStub('id_item');

        $item = new ProjectionItemIterableStub('id_item');
        $item->setStuff('itn');
        $item->storeData(['id' => 'beiga', 'value' => 1000]);

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

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->add($item);

        $this->assertEquals(
            '{"key":"test_iterable_id_item","stuff":"itn","data":{"id":"beiga","value":1000}}',
            $cacheItemStub->get()
        );
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }

    public function testWhenAddFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection item on cache pool');

        $item = new ProjectionItemStub('id_item');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
            ->willReturn(new CacheItemStub('test_id_item'));

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item)
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->add($item);
    }

    public function testAddDeferred(): void
    {
        $cacheItemStub = new CacheItemStub('id_item');

        $item = new ProjectionItemStub('id_item');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
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
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->addDeferred($item);

        $this->assertEquals('serialized_projection_item_dummy', $cacheItemStub->get());
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }

    public function testWhenAddDeferredFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to save deferred projection item on cache pool');

        $item = new ProjectionItemStub('id_item');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
            ->willReturn(new CacheItemStub('test_id_item'));

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
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->addDeferred($item);
    }

    public function testGetExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub('id_item');
        $projectionItemOnCache = new ProjectionItemStub('id_item');

        $cacheItem = (new CacheItemStub('id_item'))
            ->setHit()
            ->set('serialized_projection_item');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->with('serialized_projection_item', $projectionItem::class)
            ->willReturn($projectionItemOnCache);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $projectionItem = $cachePoolProjectionRepository->get($projectionItem);

        $this->assertEquals($projectionItemOnCache, $projectionItem);
    }

    public function testGetNonExistentItem(): void
    {
        $projectionItem = new ProjectionItemStub('id_item');

        $cacheItem = (new CacheItemStub('id_item'))
            ->setNotHit();

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with($projectionItem->getKey())
            ->willReturn($cacheItem);

        $this->serializer
            ->expects($this->never())
            ->method('deserialize');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);

        $this->assertNull($cachePoolProjectionRepository->get($projectionItem));
    }

    public function testDelete(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willReturn(true);

        $item = new ProjectionItemStub('id_item');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->delete($item);
    }

    public function testWhenDeleteFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection item on cache pool');

        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willReturn(false);

        $item = new ProjectionItemStub('id_item');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->delete($item);
    }

    public function testFlush(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->flush();
    }

    public function testWhenFlushFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to add projection items on cache pool by flush');

        $this->cachePool
            ->expects($this->once())
            ->method('commit')
            ->willReturn(false);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->flush();
    }

    public function testDeleteByTags(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['tag_1', 'tag_2'])
            ->willReturn(true);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->deleteByTags(new Tags(new Tag('tag_1'), new Tag('tag_2')));
    }

    public function testWhenDeleteByTagsFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection items on cache pool based on tag');

        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturn(false);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->deleteByTags(new Tags());
    }

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(TagAwareAdapterInterface::class);
        $this->serializer = $this->createMock(CacheSerializerInterface::class);
    }
}

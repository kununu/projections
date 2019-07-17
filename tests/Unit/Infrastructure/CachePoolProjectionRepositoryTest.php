<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Infrastructure;

use Kununu\Projections\Infrastructure\CachePoolProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class CachePoolProjectionRepositoryTest extends TestCase
{
    /** @var TagAwareAdapterInterface|MockObject */
    private $cachePool;

    /** @var SerializerInterface|MockObject */
    private $serializer;

    public function testAdd(): void
    {
        $cacheItemStub = new CacheItemStub();

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

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
            ->with($item, 'json')
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->add($item);

        $this->assertEquals('serialized_projection_item_dummy', $cacheItemStub->get());
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }

    public function testWhenAddFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not possible to add projection item on cache pool');

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
            ->willReturn(new CacheItemStub());

        $this->cachePool
            ->expects($this->once())
            ->method('save')
            ->willReturn(false);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($item, 'json')
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->add($item);
    }

    public function testAddDeferred(): void
    {
        $cacheItemStub = new CacheItemStub();

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

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
            ->with($item, 'json')
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->addDeferred($item);

        $this->assertEquals('serialized_projection_item_dummy', $cacheItemStub->get());
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }

    public function testWhenAddDeferredFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not possible to save deferred projection item on cache pool');

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

        $this->cachePool
            ->expects($this->once())
            ->method('getItem')
            ->with('test_id_item')
            ->willReturn(new CacheItemStub());

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
            ->with($item, 'json')
            ->willReturn('serialized_projection_item_dummy');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->addDeferred($item);
    }

    public function testGetExistentItem(): void
    {
        $projectionItem = (new ProjectionItemDummy('id_item'));

        $projectionItemOnCache = (new ProjectionItemDummy('id_item'))->setStuff('stuff');

        $cacheItem = (new CacheItemStub())
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
            ->with('serialized_projection_item', get_class($projectionItem), 'json')
            ->willReturn($projectionItemOnCache);

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $projectionItem = $cachePoolProjectionRepository->get($projectionItem);

        $this->assertEquals($projectionItemOnCache, $projectionItem);
    }

    public function testGetNonExistentItem(): void
    {
        $projectionItem = (new ProjectionItemDummy('id_item'));

        $cacheItem = (new CacheItemStub())
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

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

        $cachePoolProjectionRepository = new CachePoolProjectionRepository($this->cachePool, $this->serializer);
        $cachePoolProjectionRepository->delete($item);
    }

    public function testWhenDeleteFails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not possible to delete projection item on cache pool');

        $this->cachePool
            ->expects($this->once())
            ->method('deleteItem')
            ->willReturn(false);

        $item = new ProjectionItemDummy('id_item');
        $item->setStuff('itn');

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
        $this->expectException(\RuntimeException::class);
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
        $this->expectException(\RuntimeException::class);
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
        $this->serializer = $this->createMock(SerializerInterface::class);
    }
}

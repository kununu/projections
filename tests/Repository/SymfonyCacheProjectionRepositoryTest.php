<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Repository\SymfonyCacheProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use Kununu\Projections\Tests\Stubs\CacheItem\CacheItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

final class SymfonyCacheProjectionRepositoryTest extends AbstractProjectionRepositoryTestCase
{
    public function testDeleteByTags(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['tag_1', 'tag_2'])
            ->willReturn(true);

        $this->getProjectionRepository()->deleteByTags(new Tags(new Tag('tag_1'), new Tag('tag_2')));
    }

    public function testWhenDeleteByTagsFails(): void
    {
        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection items on cache pool based on tag');

        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->willReturn(false);

        $this->getProjectionRepository()->deleteByTags(new Tags());
    }

    protected function extraAssertionsForAdd(CacheItemStub $cacheItemStub): void
    {
        $this->assertTags($cacheItemStub);
    }

    protected function extraAssertionsForAddIterable(CacheItemStub $cacheItemStub): void
    {
        $this->assertTags($cacheItemStub);
    }

    protected function extraAssertionsForAddDeferred(CacheItemStub $cacheItemStub): void
    {
        $this->assertTags($cacheItemStub);
    }

    protected function getCachePool(): MockObject|TagAwareAdapterInterface
    {
        if (null === $this->cachePool) {
            $this->cachePool = $this->createMock(TagAwareAdapterInterface::class);
        }

        return $this->cachePool;
    }

    protected function getProjectionRepository(): ProjectionRepositoryInterface
    {
        return new SymfonyCacheProjectionRepository($this->getCachePool(), $this->serializer);
    }

    private function assertTags(CacheItemStub $cacheItemStub): void
    {
        $this->assertEquals(['test', 'kununu', 'id_item'], $cacheItemStub->getTags());
    }
}

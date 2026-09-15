<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Repository;

use BadMethodCallException;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Repository\Psr6CacheProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemPoolInterface;

final class Psr6CacheProjectionRepositoryTest extends AbstractProjectionRepositoryTestCase
{
    public function testDeleteByTags(): void
    {
        $this->expectsCachePoolNotToBeInvoked();
        $this->expectsSerializerNotToBeInvoked();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessageIs('PSR-6 does not support tags!');

        $this->projectionRepository->deleteByTags(new Tags(new Tag('tag_1'), new Tag('tag_2')));
    }

    protected function getCachePool(): MockObject&CacheItemPoolInterface
    {
        $this->cachePool ??= $this->createMock(CacheItemPoolInterface::class);

        return $this->cachePool;
    }

    protected function getProjectionRepository(): ProjectionRepositoryInterface
    {
        return new Psr6CacheProjectionRepository($this->getCachePool(), $this->serializer);
    }
}

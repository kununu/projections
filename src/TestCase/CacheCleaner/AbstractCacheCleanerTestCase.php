<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\CacheCleaner;

use Kununu\Projections\CacheCleaner\CacheCleanerInterface;
use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Repository\CachePoolProjectionRepository;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

abstract class AbstractCacheCleanerTestCase extends TestCase
{
    protected const TAGS = [];

    protected MockObject|TagAwareAdapterInterface $cachePool;
    protected MockObject|LoggerInterface $logger;
    protected CacheCleanerInterface $cacheCleaner;

    public function testCacheCleaner(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(static::TAGS)
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::INFO,
                'Deleting tagged cache items',
                ['tags' => static::TAGS, 'class' => $this->cacheCleaner::class]
            );

        $this->cacheCleaner->clear();
    }

    public function testCacheCleanerFail(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(static::TAGS)
            ->willReturn(false);

        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::INFO,
                'Deleting tagged cache items',
                ['tags' => static::TAGS, 'class' => $this->cacheCleaner::class]
            );

        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection items on cache pool based on tag');

        $this->cacheCleaner->clear();
    }

    abstract protected function getCacheCleaner(
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ): CacheCleanerInterface;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(TagAwareAdapterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cacheCleaner = $this->getCacheCleaner(
            new CachePoolProjectionRepository($this->cachePool, $this->createMock(CacheSerializerInterface::class)),
            $this->logger
        );
    }
}

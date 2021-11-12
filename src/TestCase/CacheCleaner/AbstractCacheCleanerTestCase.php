<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\CacheCleaner;

use JMS\Serializer\SerializerInterface;
use Kununu\Projections\CacheCleaner\CacheCleaner;
use Kununu\Projections\Exception\ProjectionException;
use Kununu\Projections\ProjectionRepository;
use Kununu\Projections\Repository\CachePoolProjectionRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

abstract class AbstractCacheCleanerTestCase extends TestCase
{
    protected const TAGS = [];

    protected $cachePool;
    protected $logger;
    protected $cacheCleaner;

    public function testCacheCleaner(): void
    {
        $this->cachePool
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(static::TAGS)
            ->willReturn(true);

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Deleting tagged cache items', ['tags' => static::TAGS]);

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
            ->method('info')
            ->with('Deleting tagged cache items', ['tags' => static::TAGS]);

        $this->expectException(ProjectionException::class);
        $this->expectExceptionMessage('Not possible to delete projection items on cache pool based on tag');

        $this->cacheCleaner->clear();
    }

    abstract protected function getCacheCleaner(ProjectionRepository $projectionRepository, LoggerInterface $logger): CacheCleaner;

    protected function setUp(): void
    {
        $this->cachePool = $this->createMock(TagAwareAdapterInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->cacheCleaner = $this->getCacheCleaner(
            new CachePoolProjectionRepository($this->cachePool, $this->createMock(SerializerInterface::class)),
            $this->logger
        );
    }
}

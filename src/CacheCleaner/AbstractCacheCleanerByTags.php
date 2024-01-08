<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\Tags;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractCacheCleanerByTags implements CacheCleanerInterface
{
    public function __construct(
        private ProjectionRepositoryInterface $projectionRepository,
        private LoggerInterface $logger,
        private string $logLevel = LogLevel::INFO
    ) {
    }

    public function clear(): void
    {
        $tags = $this->getTags();

        $this->logger->log(
            $this->logLevel,
            'Deleting tagged cache items',
            ['tags' => $tags->raw(), 'class' => static::class]
        );
        $this->projectionRepository->deleteByTags($tags);
    }

    abstract protected function getTags(): Tags;
}

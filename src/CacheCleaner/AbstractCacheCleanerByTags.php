<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\Tags;
use Psr\Log\LoggerInterface;

abstract class AbstractCacheCleanerByTags implements CacheCleanerInterface
{
    private $projectionRepository;
    private $logger;

    public function __construct(ProjectionRepositoryInterface $projectionRepository, LoggerInterface $logger)
    {
        $this->projectionRepository = $projectionRepository;
        $this->logger = $logger;
    }

    public function clear(): void
    {
        $tags = $this->getTags();

        $this->logger->info('Deleting tagged cache items', ['tags' => $tags->raw()]);
        $this->projectionRepository->deleteByTags($tags);
    }

    abstract protected function getTags(): Tags;
}

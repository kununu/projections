<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

use Kununu\Projections\ProjectionRepository;
use Kununu\Projections\Tag\Tags;
use Psr\Log\LoggerInterface;

abstract class AbstractCacheCleanerByTags implements CacheCleaner
{
    private $projectionRepository;
    private $logger;

    public function __construct(ProjectionRepository $projectionRepository, LoggerInterface $logger)
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

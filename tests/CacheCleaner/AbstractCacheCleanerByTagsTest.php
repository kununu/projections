<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\CacheCleaner;

use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;
use Kununu\Projections\CacheCleaner\CacheCleanerInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\ProjectionTagGenerator;
use Kununu\Projections\Tag\Tags;
use Kununu\Projections\TestCase\CacheCleaner\AbstractCacheCleanerTestCase;
use Psr\Log\LoggerInterface;

final class AbstractCacheCleanerByTagsTest extends AbstractCacheCleanerTestCase
{
    protected const TAGS = ['my-tag1', 'my-tag2'];

    protected function getCacheCleaner(
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ): CacheCleanerInterface {
        return new class($projectionRepository, $logger) extends AbstractCacheCleanerByTags {
            use ProjectionTagGenerator;

            protected function getTags(): Tags
            {
                return $this->createTagsFromArray('my-tag1', 'my-tag2');
            }
        };
    }
}

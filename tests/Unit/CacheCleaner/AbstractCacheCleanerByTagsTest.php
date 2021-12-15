<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\CacheCleaner;

use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;
use Kununu\Projections\CacheCleaner\CacheCleaner;
use Kununu\Projections\ProjectionRepository;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use Kununu\Projections\TestCase\CacheCleaner\AbstractCacheCleanerTestCase;
use Psr\Log\LoggerInterface;

final class AbstractCacheCleanerByTagsTest extends AbstractCacheCleanerTestCase
{
    protected const TAGS = ['my-tag1', 'my-tag2'];

    protected function getCacheCleaner(ProjectionRepository $projectionRepository, LoggerInterface $logger): CacheCleaner
    {
        return new class($projectionRepository, $logger) extends AbstractCacheCleanerByTags {
            protected function getTags(): Tags
            {
                return new Tags(new Tag('my-tag1'), new Tag('my-tag2'));
            }
        };
    }
}

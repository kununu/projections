<?php
declare(strict_types=1);

namespace Kununu\Projections\Repository;

use BadMethodCallException;
use Kununu\Projections\Tag\Tags;

final class Psr6CacheProjectionRepository extends AbstractProjectionRepository
{
    public function deleteByTags(Tags $tags): void
    {
        throw new BadMethodCallException('PSR-6 does not support tags!');
    }
}

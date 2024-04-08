<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\CacheItem;

use Psr\Cache\CacheItemInterface;

interface TaggableCacheItemInterface extends CacheItemInterface
{
    public function getTags(): array;
}

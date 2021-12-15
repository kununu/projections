<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

final class CacheCleanerChain implements CacheCleaner
{
    private $cacheCleaners;

    public function __construct(CacheCleaner ...$cacheCleaners)
    {
        $this->cacheCleaners = $cacheCleaners;
    }

    public function clear(): void
    {
        foreach ($this->cacheCleaners as $cacheCleaner) {
            $cacheCleaner->clear();
        }
    }
}

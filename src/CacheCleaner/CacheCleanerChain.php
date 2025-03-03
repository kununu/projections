<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

final readonly class CacheCleanerChain implements CacheCleanerInterface
{
    /** @var array<CacheCleanerInterface> */
    private array $cacheCleaners;

    public function __construct(CacheCleanerInterface ...$cacheCleaners)
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

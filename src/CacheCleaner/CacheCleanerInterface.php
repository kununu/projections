<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

interface CacheCleanerInterface
{
    public function clear(): void;
}

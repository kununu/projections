<?php
declare(strict_types=1);

namespace Kununu\Projections\CacheCleaner;

interface CacheCleaner
{
    public function clear(): void;
}

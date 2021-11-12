<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\CacheCleaner;

use Kununu\Projections\CacheCleaner\CacheCleaner;
use Kununu\Projections\CacheCleaner\CacheCleanerChain;
use PHPUnit\Framework\TestCase;

final class CacheCleanerChainTest extends TestCase
{
    public function testCacheCleanerChain(): void
    {
        $chain = new CacheCleanerChain(
            $this->createCacheCleaner(),
            $this->createCacheCleaner(),
            $this->createCacheCleaner()
        );

        $chain->clear();
    }

    private function createCacheCleaner(): CacheCleaner
    {
        $cleaner = $this->createMock(CacheCleaner::class);

        $cleaner
            ->expects($this->once())
            ->method('clear');

        return $cleaner;
    }
}

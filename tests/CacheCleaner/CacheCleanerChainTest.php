<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\CacheCleaner;

use Kununu\Projections\CacheCleaner\CacheCleanerChain;
use Kununu\Projections\CacheCleaner\CacheCleanerInterface;
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

    private function createCacheCleaner(): CacheCleanerInterface
    {
        $cleaner = $this->createMock(CacheCleanerInterface::class);

        $cleaner
            ->expects($this->once())
            ->method('clear');

        return $cleaner;
    }
}

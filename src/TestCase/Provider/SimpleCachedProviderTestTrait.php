<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\Provider;

use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;

trait SimpleCachedProviderTestTrait
{
    private function configureCachedProvider(
        MockObject&ProjectionRepositoryInterface $projectionRepository,
        MockObject $originalProvider,
        string $method,
        bool $expectCacheMiss,
        ProjectionItemInterface $searchItem,
        ProjectionItemInterface $sourceItem,
        mixed $originalResult,
    ): void {
        $projectionRepository
            ->expects($this->once())
            ->method('get')
            ->with($searchItem)
            ->willReturn($expectCacheMiss ? null : $sourceItem);

        if ($expectCacheMiss) {
            $originalProvider
                ->expects($this->once())
                ->method($method)
                ->willReturn($originalResult);
        } else {
            $originalProvider
                ->expects($this->never())
                ->method($method);
        }

        if ($expectCacheMiss && $originalResult) {
            $projectionRepository
                ->expects($this->once())
                ->method('add')
                ->with($sourceItem);
        } else {
            $projectionRepository
                ->expects($this->never())
                ->method('add');
        }
    }
}

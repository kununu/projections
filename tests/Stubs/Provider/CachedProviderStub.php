<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Provider\AbstractCachedProvider;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Psr\Log\LoggerInterface;

final class CachedProviderStub extends AbstractCachedProvider implements ProviderStubInterface
{
    public function __construct(
        private readonly ProviderStubInterface $provider,
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger,
    ) {
        parent::__construct($projectionRepository, $logger);
    }

    public function getData(string $id): ?iterable
    {
        return $this->getAndCacheData(
            new ProjectionItemIterableStub($id),
            fn(): ?iterable => $this->provider->getData($id),
            function(ProjectionItemIterableInterface $item, iterable $data): ?iterable {
                assert($item instanceof ProjectionItemIterableStub);

                return $item->getKey() === 'test_iterable_2'
                    ? null
                    : $data;
            }
        );
    }

    public function invalidateItem(string $id): void
    {
        $this->invalidateCacheItemByKey(new ProjectionItemIterableStub($id));
    }
}

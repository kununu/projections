<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Provider\AbstractCachedProvider;
use Psr\Log\LoggerInterface;

final class MyCachedProviderStub extends AbstractCachedProvider implements MyProviderStubInterface
{

    public function __construct(
        private MyProviderStubInterface $provider,
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($projectionRepository, $logger);
    }

    public function getData(int $id): ?iterable
    {
        return $this->getAndCacheData(
            new MyStubProjectionItem($id),
            fn(): ?iterable => $this->provider->getData($id),
            function(ProjectionItemIterableInterface $item, iterable $data): ?iterable {
                assert($item instanceof MyStubProjectionItem);

                return $item->getKey() === 'my_data_3'
                    ? null
                    : $data;
            }
        );
    }

    public function invalidateItem(int $id): void
    {
        $this->invalidateCacheItemByKey(new MyStubProjectionItem($id));
    }
}

<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Provider\AbstractCachedProvider;
use Psr\Log\LoggerInterface;

final class MyCachedProviderStub extends AbstractCachedProvider implements MyProviderStubInterface
{
    private $provider;

    public function __construct(
        MyProviderStubInterface $provider,
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($projectionRepository, $logger);
        $this->provider = $provider;
    }

    public function getData(int $id): ?iterable
    {
        return $this->getAndCacheData(
            new MyStubProjectionItem($id),
            function() use ($id): ?iterable {
                return $this->provider->getData($id);
            },
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

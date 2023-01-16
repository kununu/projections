<?php
declare(strict_types=1);

namespace Kununu\Projections\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractCachedProvider
{
    private const CACHE_KEY = 'cache_key';
    private const DATA = 'data';

    private $projectionRepository;
    private $logger;

    public function __construct(ProjectionRepositoryInterface $projectionRepository, LoggerInterface $logger)
    {
        $this->projectionRepository = $projectionRepository;
        $this->logger = $logger;
    }

    protected function getAndCacheData(
        ProjectionItemIterableInterface $item,
        callable $dataGetter,
        callable ...$preProjections
    ): ?iterable {
        $this->logger->info('Getting data from cache', [self::CACHE_KEY => $item->getKey()]);

        $projectedItem = $this->projectionRepository->get($item);
        if ($projectedItem instanceof ProjectionItemIterableInterface) {
            $this->logger->info(
                'Item hit! Returning data from the cache',
                [
                    self::CACHE_KEY => $item->getKey(),
                    self::DATA      => $projectedItem->data(),
                ]
            );

            return $projectedItem->data();
        }

        $this->logger->info('Item not hit! Fetching data...', [self::CACHE_KEY => $item->getKey()]);

        $data = $dataGetter();

        if (is_iterable($data)) {
            // Manipulate data before projection if callers are defined
            foreach ($preProjections as $preProjection) {
                $data = $preProjection($item, $data);
                // If pre-projection callable returns null means we do not have relevant information.
                // We will not store the item in the cache and will break the pre-projection chain
                if (null === $data) {
                    $this->logger->info('Item not stored in the cache!', [self::DATA => $data]);
                    $data = null;

                    break;
                }
            }

            if (null !== $data) {
                $this->projectionRepository->add($item->storeData($data));
                $this->logger->info(
                    'Item saved into cache and returned',
                    [self::CACHE_KEY => $item->getKey(), self::DATA => $data]
                );
            }
        } else {
            $this->logger->info('No data fetched and stored into cache!', [self::CACHE_KEY => $item->getKey()]);
            $data = null;
        }

        return $data;
    }

    protected function invalidateCacheItemByKey(ProjectionItemIterableInterface $projectionItem): self
    {
        $this->logger->info('Deleting cache item', ['cache_key' => $projectionItem->getKey()]);
        $this->projectionRepository->delete($projectionItem);

        return $this;
    }
}

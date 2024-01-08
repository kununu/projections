<?php
declare(strict_types=1);

namespace Kununu\Projections\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractCachedProvider
{
    private const CACHE_KEY = 'cache_key';
    private const CLASS_KEY = 'class';
    private const DATA_KEY = 'data';

    public function __construct(
        private ProjectionRepositoryInterface $projectionRepository,
        private LoggerInterface $logger,
        private string $logLevel = LogLevel::INFO
    ) {
    }

    protected function logger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getAndCacheData(
        ProjectionItemIterableInterface $item,
        callable $dataGetter,
        callable ...$preProjections
    ): ?iterable {
        $key = $item->getKey();

        $this->log('Getting data from cache', $key);

        $projectedItem = $this->projectionRepository->get($item);
        if ($projectedItem instanceof ProjectionItemIterableInterface) {
            $data = $projectedItem->data();

            $this->log('Item hit! Returning data from the cache', $key, $data);

            return $data;
        }

        $this->log('Item not hit! Fetching data...', $key);

        $data = $dataGetter();

        if (is_iterable($data)) {
            // Manipulate data before projection if callers are defined
            foreach ($preProjections as $preProjection) {
                $data = $preProjection($item, $data);
                // If pre-projection callable returns null means we do not have relevant information.
                // We will not store the item in the cache and will break the pre-projection chain
                if (null === $data) {
                    $this->log('Item not stored in the cache!', $key);

                    break;
                }
            }

            if (null !== $data) {
                $this->projectionRepository->add($item->storeData($data));
                $this->log('Item saved into cache and returned', $key, $data);
            }
        } else {
            $this->log('No data fetched and stored into cache!', $key);
            $data = null;
        }

        return $data;
    }

    protected function invalidateCacheItemByKey(ProjectionItemIterableInterface $projectionItem): self
    {
        $this->log('Deleting cache item', $projectionItem->getKey());
        $this->projectionRepository->delete($projectionItem);

        return $this;
    }

    private function log(string $message, string $cacheKey, mixed $data = null): void
    {
        $this->logger->log(
            $this->logLevel,
            $message,
            array_merge(
                [self::CACHE_KEY => $cacheKey, self::CLASS_KEY => static::class],
                $data ? [self::DATA_KEY => $data] : []
            )
        );
    }
}

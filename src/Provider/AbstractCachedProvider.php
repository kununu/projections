<?php
declare(strict_types=1);

namespace Kununu\Projections\Provider;

use Kununu\Projections\ProjectionItemIterable;
use Kununu\Projections\ProjectionRepository;
use Psr\Log\LoggerInterface;

abstract class AbstractCachedProvider
{
    private const CACHE_KEY = 'cache_key';
    private const DATA = 'data';

    private $projectionRepository;
    private $logger;

    public function __construct(ProjectionRepository $projectionRepository, LoggerInterface $logger)
    {
        $this->projectionRepository = $projectionRepository;
        $this->logger = $logger;
    }

    protected function getAndCacheData(ProjectionItemIterable $item, callable $dataGetter): ?iterable
    {
        $this->logger->info('Getting data from cache', [self::CACHE_KEY => $item->getKey()]);

        $projectedItem = $this->projectionRepository->get($item);
        if ($projectedItem instanceof ProjectionItemIterable) {
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
            $this->projectionRepository->add($item->storeData($data));
            $this->logger->info('Item saved into cache and returned', [self::CACHE_KEY => $item->getKey(), self::DATA => $data]);

            return $data;
        }

        $this->logger->info('No data fetched and stored into cache!', [self::CACHE_KEY => $item->getKey()]);

        return null;
    }
}

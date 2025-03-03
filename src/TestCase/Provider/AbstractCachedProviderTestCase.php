<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Provider\AbstractCachedProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class AbstractCachedProviderTestCase extends TestCase
{
    protected const array METHODS = [];

    protected (MockObject&ProjectionRepositoryInterface)|null $projectionRepository = null;
    protected (MockObject&LoggerInterface)|null $logger = null;

    #[DataProvider('getAndCacheDataDataProvider')]
    public function testGetAndCacheData(
        object|callable $originalProvider,
        string $method,
        array $args,
        ProjectionItemIterableInterface $item,
        ?ProjectionItemIterableInterface $projectedItem,
        ?iterable $providerData,
        ?iterable $expectedResult = null,
        ?callable $preProjection = null,
    ): void {
        $provider = match (true) {
            is_callable($originalProvider) => $originalProvider($this, $this->dataName(), $method, $args),
            default                        => $originalProvider,
        };

        // Get from cache
        ($repository = $this->getProjectionRepository())
            ->expects($this->once())
            ->method('get')
            ->with($item)
            ->willReturn($projectedItem);

        // Cache miss
        if (null === $projectedItem && is_iterable($providerData)) {
            $itemToProject = (clone $item)->storeData($providerData);
            if (is_callable($preProjection)) {
                $itemToProject = $preProjection($itemToProject);
            }

            if ($expectedResult && $itemToProject) {
                // Add data to the cache
                $repository
                    ->expects($this->once())
                    ->method('add')
                    ->with($itemToProject);
            } else {
                $repository
                    ->expects($this->never())
                    ->method('add');
            }
        }

        $result = call_user_func_array([$this->getProvider($provider), $method], $args);

        self::assertEquals($expectedResult, $result);
    }

    public static function getAndCacheDataDataProvider(): array
    {
        $data = [];
        foreach (static::METHODS as $method) {
            $methodDataProvider = sprintf('%sDataProvider', $method);
            $methodData = forward_static_call_array([static::class, $methodDataProvider], []);
            foreach ($methodData as $dataName => $values) {
                $data[sprintf('%s_%s', $method, $dataName)] = $values;
            }
        }

        return $data;
    }

    abstract protected function getProvider(mixed $originalProvider): AbstractCachedProvider;

    protected function createMockedOriginalProvider(
        string $providerClass,
        string $method,
        array $args,
        bool $expected,
        ?iterable $data = null,
    ): MockObject {
        $provider = $this->createMock($providerClass);

        if ($expected) {
            $provider
                ->expects($this->once())
                ->method($method)
                ->with(...$args)
                ->willReturn($data);
        } else {
            $provider
                ->expects($this->never())
                ->method($method);
        }

        return $provider;
    }

    protected function getProjectionRepository(): MockObject&ProjectionRepositoryInterface
    {
        if (null === $this->projectionRepository) {
            $this->projectionRepository = $this->createMock(ProjectionRepositoryInterface::class);
        }

        return $this->projectionRepository;
    }

    protected function getLogger(): MockObject&LoggerInterface
    {
        if (null === $this->logger) {
            $this->logger = $this->createMock(LoggerInterface::class);
        }

        return $this->logger;
    }
}

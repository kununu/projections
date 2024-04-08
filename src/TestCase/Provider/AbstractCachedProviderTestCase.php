<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Provider\AbstractCachedProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class AbstractCachedProviderTestCase extends TestCase
{
    protected const METHODS = [];

    protected MockObject|ProjectionRepositoryInterface|null $projectionRepository = null;
    protected MockObject|LoggerInterface|null $logger = null;

    #[DataProvider('getAndCacheDataDataProvider')]
    public function testGetAndCacheData(
        mixed $originalProvider,
        string $method,
        array $args,
        ProjectionItemIterableInterface $item,
        ?ProjectionItemIterableInterface $projectedItem,
        ?iterable $providerData,
        ?iterable $expectedResult = null,
        ?callable $preProjection = null
    ): void {
        // Get from cache
        ($repository = $this->getProjectionRepository())
            ->expects(self::once())
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
                    ->expects(self::once())
                    ->method('add')
                    ->with($itemToProject);
            } else {
                $repository
                    ->expects(self::never())
                    ->method('add');
            }
        }

        $result = call_user_func_array([$this->getProvider($originalProvider), $method], $args);

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

    protected static function createExternalProvider(
        string $providerClass,
        string $method,
        array $args,
        bool $expected,
        ?iterable $data
    ): MockObject {
        $provider = (new MockBuilder(new static(sprintf('%s::%s', $providerClass, $method)), $providerClass))
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->onlyMethods([$method])
            ->getMock();

        $invocationMocker = $provider
            ->expects($expected ? self::once() : self::never())
            ->method($method)
            ->with(...$args);

        if ($expected) {
            $invocationMocker->willReturn($data);
        }

        return $provider;
    }

    protected function getProjectionRepository(): MockObject|ProjectionRepositoryInterface
    {
        if (null === $this->projectionRepository) {
            $this->projectionRepository = $this->createMock(ProjectionRepositoryInterface::class);
        }

        return $this->projectionRepository;
    }

    protected function getLogger(): MockObject|LoggerInterface
    {
        if (null === $this->logger) {
            $this->logger = $this->createMock(LoggerInterface::class);
        }

        return $this->logger;
    }
}

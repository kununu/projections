<?php
declare(strict_types=1);

namespace Kununu\Projections\TestCase\Provider;

use Kununu\Projections\ProjectionItemIterable;
use Kununu\Projections\ProjectionRepository;
use Kununu\Projections\Provider\AbstractCachedProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

abstract class CachedProviderTestCase extends TestCase
{
    protected const METHODS = [];

    protected $projectionRepository;
    protected $logger;

    /**
     * @dataProvider getAndCacheDataDataProvider
     *
     * @param                             $originalProvider
     * @param string                      $method
     * @param array                       $args
     * @param ProjectionItemIterable      $item
     * @param ProjectionItemIterable|null $projectedItem
     * @param iterable|null               $providerData
     */
    public function testGetAndCacheData(
        $originalProvider,
        string $method,
        array $args,
        ProjectionItemIterable $item,
        ?ProjectionItemIterable $projectedItem,
        ?iterable $providerData
    ): void {
        // Get from cache
        ($repository = $this->getProjectionRepository())
            ->expects($this->once())
            ->method('get')
            ->with($item)
            ->willReturn($projectedItem);

        // Cache miss
        if (null === $projectedItem && is_iterable($providerData)) {
            // Get data from provider
            $repository
                ->expects($this->once())
                ->method('add')
                ->with((clone $item)->storeData($providerData));
        }

        $result = call_user_func_array([$this->getProvider($originalProvider), $method], $args);

        $this->assertEquals($providerData, $result);
    }

    public function getAndCacheDataDataProvider(): array
    {
        $data = [];
        foreach (static::METHODS as $method) {
            $methodDataProvider = sprintf('%sDataProvider', $method);
            $methodData = call_user_func_array([$this, $methodDataProvider], []);
            foreach ($methodData as $dataName => $values) {
                $data[sprintf('%s_%s', $method, $dataName)] = $values;
            }
        }

        return $data;
    }

    abstract protected function getProvider($originalProvider): AbstractCachedProvider;

    /**
     * @param string        $providerClass
     * @param string        $method
     * @param array         $args
     * @param bool          $expected
     * @param iterable|null $data
     *
     * @return mixed|MockObject
     */
    protected function createExternalProvider(string $providerClass, string $method, array $args, bool $expected, ?iterable $data)
    {
        $provider = $this->createMock($providerClass);
        $invocationMocker = $provider
            ->expects($expected ? $this->once() : $this->never())
            ->method($method)
            ->with(...$args);

        if ($expected) {
            $invocationMocker->willReturn($data);
        }

        return $provider;
    }

    /**
     * @return ProjectionRepository|MockObject
     */
    protected function getProjectionRepository(): ProjectionRepository
    {
        if (null === $this->projectionRepository) {
            $this->projectionRepository = $this->createMock(ProjectionRepository::class);
        }

        return $this->projectionRepository;
    }

    /**
     * @return LoggerInterface|MockObject
     */
    protected function getLogger(): LoggerInterface
    {
        if (null === $this->logger) {
            $this->logger = $this->createMock(LoggerInterface::class);
        }

        return $this->logger;
    }
}

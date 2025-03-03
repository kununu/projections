<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

use Kununu\Projections\TestCase\Provider\AbstractCachedProviderTestCase;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Kununu\Projections\Tests\Stubs\Provider\CachedProviderStub;
use Kununu\Projections\Tests\Stubs\Provider\ProviderStub;
use Kununu\Projections\Tests\Stubs\Provider\ProviderStubInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

final class AbstractCachedProviderTest extends AbstractCachedProviderTestCase
{
    protected const array METHODS = [
        self::METHOD_GET_DATA,
    ];

    private const string METHOD_GET_DATA = 'getData';
    private const string ID_1 = '1';
    private const string ID_2 = '2';
    private const string ID_3 = '3';
    private const array DATA = [
        'id'   => self::ID_1,
        'name' => 'The Name of 1',
        'age'  => 21,
    ];
    private const array DATA_CACHED = [
        'id'   => self::ID_1,
        'name' => 'The Name of 1 cached',
        'age'  => 21,
    ];

    public static function getDataDataProvider(): array
    {
        $originalProvider = new ProviderStub();

        return [
            'cache_miss_and_external_provider_gives_data'                                           => [
                'originalProvider' => $originalProvider,
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_1],
                'item'             => new ProjectionItemIterableStub(self::ID_1),
                'projectedItem'    => null,
                'providerData'     => self::DATA,
                'expectedResult'   => self::DATA,
                'preProjection'    => null,
            ],
            'cache_miss_and_data_from_external_provider_not_relevant'                               => [
                'originalProvider' => $originalProvider,
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_2],
                'item'             => new ProjectionItemIterableStub(self::ID_2),
                'projectedItem'    => null,
                'providerData'     => self::DATA,
                'expectedResult'   => null,
                'preProjection'    => null,
            ],
            'cache_miss_and_data_from_external_provider_not_relevant_because_of_tests_manipulation' => [
                'originalProvider' => $originalProvider,
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_3],
                'item'             => new ProjectionItemIterableStub(self::ID_3),
                'projectedItem'    => null,
                'providerData'     => self::DATA,
                'expectedResult'   => null,
                'preProjection'    => fn() => null,
            ],
            'cache_miss_and_no_data_from_external_provider'                                         => [
                'originalProvider' => $originalProvider,
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_3],
                'item'             => new ProjectionItemIterableStub(self::ID_3),
                'projectedItem'    => null,
                'providerData'     => null,
                'expectedResult'   => null,
                'preProjection'    => null,
            ],
            'cache_hit'                                                                             => [
                'originalProvider' => $originalProvider,
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_1],
                'item'             => new ProjectionItemIterableStub(self::ID_1),
                'projectedItem'    => (new ProjectionItemIterableStub(self::ID_1))->storeData(self::DATA_CACHED),
                'providerData'     => self::DATA_CACHED,
                'expectedResult'   => self::DATA_CACHED,
            ],
            'cache_miss_using_mocked_original_provider'                                             => [
                'originalProvider' => static fn(self $test): MockObject => $test->createMockedOriginalProvider(
                    providerClass: ProviderStubInterface::class,
                    method: self::METHOD_GET_DATA,
                    args: [self::ID_1],
                    expected: true,
                    data: self::DATA
                ),
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_1],
                'item'             => new ProjectionItemIterableStub(self::ID_1),
                'projectedItem'    => null,
                'providerData'     => self::DATA,
                'expectedResult'   => self::DATA,
            ],
            'cache_hit_using_mocked_original_provider'                                              => [
                'originalProvider' => static fn(self $test): MockObject => $test->createMockedOriginalProvider(
                    providerClass: ProviderStubInterface::class,
                    method: self::METHOD_GET_DATA,
                    args: [self::ID_1],
                    expected: false
                ),
                'method'           => self::METHOD_GET_DATA,
                'args'             => [self::ID_1],
                'item'             => new ProjectionItemIterableStub(self::ID_1),
                'projectedItem'    => (new ProjectionItemIterableStub(self::ID_1))->storeData(self::DATA_CACHED),
                'providerData'     => self::DATA_CACHED,
                'expectedResult'   => self::DATA_CACHED,
            ],
        ];
    }

    public function testInvalidateCacheItemByKey(): void
    {
        $provider = $this->getProvider(new ProviderStub());

        $this
            ->getLogger()
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::INFO,
                'Deleting cache item',
                ['cache_key' => 'test_iterable_1', 'class' => CachedProviderStub::class]
            );

        $this
            ->getProjectionRepository()
            ->expects($this->once())
            ->method('delete')
            ->with(new ProjectionItemIterableStub(self::ID_1));

        $provider->invalidateItem(self::ID_1);
    }

    public function testGetAndCacheDataDataProvider(): void
    {
        $keys = array_map(
            static fn(string $key): string => sprintf('getData_%s', $key),
            array_keys(self::getDataDataProvider())
        );

        self::assertEquals(array_combine($keys, self::getDataDataProvider()), self::getAndCacheDataDataProvider());
    }

    protected function getProvider(mixed $originalProvider): CachedProviderStub
    {
        self::assertInstanceOf(ProviderStubInterface::class, $originalProvider);

        return new CachedProviderStub($originalProvider, $this->getProjectionRepository(), $this->getLogger());
    }
}

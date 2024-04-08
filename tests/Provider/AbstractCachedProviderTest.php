<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

use Kununu\Projections\TestCase\Provider\AbstractCachedProviderTestCase;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Kununu\Projections\Tests\Stubs\Provider\CachedProviderStub;
use Kununu\Projections\Tests\Stubs\Provider\ProviderStub;
use Kununu\Projections\Tests\Stubs\Provider\ProviderStubInterface;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LogLevel;

final class AbstractCachedProviderTest extends AbstractCachedProviderTestCase
{
    protected const METHODS = [
        self::METHOD_GET_DATA,
    ];

    private const METHOD_GET_DATA = 'getData';
    private const ID_1 = '1';
    private const ID_2 = '2';
    private const ID_3 = '3';
    private const DATA = [
        'id'   => self::ID_1,
        'name' => 'The Name of 1',
        'age'  => 21,
    ];
    private const DATA_CACHED = [
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
        ];
    }

    public function testInvalidateCacheItemByKey(): void
    {
        $provider = $this->getProvider(new ProviderStub());

        $this->getLogger()
            ->expects(self::once())
            ->method('log')
            ->with(
                LogLevel::INFO,
                'Deleting cache item',
                ['cache_key' => 'test_iterable_1', 'class' => CachedProviderStub::class]
            );

        $this->getProjectionRepository()
            ->expects(self::once())
            ->method('delete')
            ->with(new ProjectionItemIterableStub(self::ID_1));

        $provider->invalidateItem(self::ID_1);
    }

    public function testCreateExternalProviderReturningData(): void
    {
        /** @var MockObject|ProviderStubInterface $externalProvider */
        $externalProvider = self::createExternalProvider(
            providerClass: ProviderStubInterface::class,
            method: self::METHOD_GET_DATA,
            args: [self::ID_1],
            expected: true,
            data: self::DATA
        );

        self::assertEquals(self::DATA, $externalProvider->getData(self::ID_1));
    }

    public function testCreateExternalProviderNotReturningData(): void
    {
        /** @var MockObject|ProviderStubInterface $externalProvider */
        $externalProvider = self::createExternalProvider(
            providerClass: ProviderStubInterface::class,
            method: self::METHOD_GET_DATA,
            args: [self::ID_1],
            expected: false,
            data: null
        );

        $this->expectException(ExpectationFailedException::class);

        self::assertNull($externalProvider->getData(self::ID_1));
    }

    public function testGetAndCacheDataDataProvider(): void
    {
        $keys = array_map(
            static fn($key): string => sprintf('getData_%s', $key),
            array_keys(self::getDataDataProvider())
        );

        self::assertEquals(array_combine($keys, self::getDataDataProvider()), self::getAndCacheDataDataProvider());
    }

    protected function getProvider(mixed $originalProvider): CachedProviderStub
    {
        return new CachedProviderStub($originalProvider, $this->getProjectionRepository(), $this->getLogger());
    }
}

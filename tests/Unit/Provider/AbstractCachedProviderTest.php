<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Provider;

use Kununu\Projections\Provider\AbstractCachedProvider;
use Kununu\Projections\TestCase\Provider\CachedProviderTestCase;

final class AbstractCachedProviderTest extends CachedProviderTestCase
{
    private const METHOD_GET_DATA = 'getData';

    protected const METHODS = [
        self::METHOD_GET_DATA,
    ];

    public function getDataDataProvider(): array
    {
        $data = [
            'id'   => 2,
            'name' => 'The Name of 2',
            'age'  => 22,
        ];

        $dataCached = [
            'id'   => 2,
            'name' => 'The Name of 2 cached',
            'age'  => 22,
        ];

        $originalProvider = new MyProviderStub();

        return [
            'cache_miss_and_data_from_external_provider'              => [
                $originalProvider,
                self::METHOD_GET_DATA,
                [2],
                new MyStubProjectionItem(2),
                null,
                $data,
            ],
            'cache_miss_and_data_from_external_provider_not_relevant' => [
                $originalProvider,
                self::METHOD_GET_DATA,
                [3],
                new MyStubProjectionItem(3),
                null,
                null,
            ],
            'cache_miss_and_no_data_from_external_provider'           => [
                $originalProvider,
                self::METHOD_GET_DATA,
                [1],
                new MyStubProjectionItem(1),
                null,
                null,
            ],
            'cache_hit'                                               => [
                $originalProvider,
                self::METHOD_GET_DATA,
                [2],
                new MyStubProjectionItem(2),
                (new MyStubProjectionItem(2))->storeData($dataCached),
                $dataCached,
            ],
        ];
    }

    public function testInvalidateCacheItemByKey(): void
    {
        $provider = $this->getProvider(new MyProviderStub());

        $this->getLogger()
            ->expects($this->once())
            ->method('info')
            ->with(
                'Deleting cache item',
                ['cache_key' => 'my_data_1']
            );

        $this->getProjectionRepository()
            ->expects($this->once())
            ->method('delete')
            ->with(new MyStubProjectionItem(1));

        $provider->invalidateItem(1);
    }

    /**
     * @param $originalProvider
     *
     * @return MyCachedProviderStub|AbstractCachedProvider
     */
    protected function getProvider($originalProvider): AbstractCachedProvider
    {
        return new MyCachedProviderStub($originalProvider, $this->getProjectionRepository(), $this->getLogger());
    }
}

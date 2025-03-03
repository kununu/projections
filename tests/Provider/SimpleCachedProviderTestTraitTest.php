<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\TestCase\Provider\SimpleCachedProviderTestTrait;
use Kununu\Projections\Tests\Stubs\ProjectionItem\ProjectionItemIterableStub;
use Kununu\Projections\Tests\Stubs\Provider\CachedProviderStub;
use Kununu\Projections\Tests\Stubs\Provider\ProviderStubInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class SimpleCachedProviderTestTraitTest extends TestCase
{
    use SimpleCachedProviderTestTrait;

    private const string METHOD = 'getData';
    private const string ID = '1';
    private const array RESULT = [
        'id'   => self::ID,
        'name' => 'The Name of 1',
        'age'  => 21,
    ];

    private MockObject&ProviderStubInterface $originalProvider;
    private MockObject&ProjectionRepositoryInterface $projectionRepository;
    private ProviderStubInterface $provider;

    #[DataProvider('getDataDataProvider')]
    public function testGetData(
        bool $expectCacheMiss,
        ?array $expected,
        ?array $originalResult,
    ): void {
        $this->configureCachedProvider(
            $this->projectionRepository,
            $this->originalProvider,
            self::METHOD,
            $expectCacheMiss,
            new ProjectionItemIterableStub(self::ID),
            (new ProjectionItemIterableStub(self::ID))->storeData(self::RESULT),
            $originalResult
        );

        self::assertEquals($expected, $this->provider->getData(self::ID));
    }

    public static function getDataDataProvider(): array
    {
        return [
            'cache_hit'                => [
                'expectCacheMiss' => false,
                'expected'        => self::RESULT,
                'originalResult'  => null,
            ],
            'cache_miss_return_schema' => [
                'expectCacheMiss' => true,
                'expected'        => self::RESULT,
                'originalResult'  => self::RESULT,
            ],
            'cache_miss_return_null'   => [
                'expectCacheMiss' => true,
                'expected'        => null,
                'originalResult'  => null,
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->originalProvider = $this->createMock(ProviderStubInterface::class);
        $this->projectionRepository = $this->createMock(ProjectionRepositoryInterface::class);
        $this->provider = new CachedProviderStub(
            $this->originalProvider,
            $this->projectionRepository,
            $this->createMock(LoggerInterface::class)
        );
    }
}

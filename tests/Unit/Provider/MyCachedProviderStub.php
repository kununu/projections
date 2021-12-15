<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Provider;

use Kununu\Projections\ProjectionRepository;
use Kununu\Projections\Provider\AbstractCachedProvider;
use Psr\Log\LoggerInterface;

final class MyCachedProviderStub extends AbstractCachedProvider implements MyProviderStubInterface
{
    private $provider;

    public function __construct(MyProviderStubInterface $provider, ProjectionRepository $projectionRepository, LoggerInterface $logger)
    {
        parent::__construct($projectionRepository, $logger);
        $this->provider = $provider;
    }

    public function getData(int $id): ?array
    {
        return $this->getAndCacheData(
            new MyStubProjectionItem($id),
            function() use ($id): ?array {
                return $this->provider->getData($id);
            }
        );
    }
}

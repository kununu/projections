<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

interface MyProviderStubInterface
{
    public function getData(int $id): ?iterable;
}

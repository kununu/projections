<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\Provider;

interface ProviderStubInterface
{
    public function getData(string $id): ?iterable;
}

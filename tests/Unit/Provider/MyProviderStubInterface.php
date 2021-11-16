<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Provider;

interface MyProviderStubInterface
{
    public function getData(int $id): ?array;
}

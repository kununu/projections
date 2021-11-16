<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Provider;

final class MyProviderStub implements MyProviderStubInterface
{
    public function getData(int $id): ?array
    {
        return
            $id === 1
                ? null
                : ['id' => $id, 'name' => sprintf('The Name of %d', $id), 'age' => 20 + $id];
    }
}

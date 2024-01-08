<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\Provider;

final class ProviderStub implements ProviderStubInterface
{
    public function getData(string $id): ?iterable
    {
        return
            $id === '1' || $id === '2'
                ? ['id' => $id, 'name' => 'The Name of 1', 'age' => 21]
                : null;
    }
}

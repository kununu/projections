<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer;

interface CacheSerializerInterface
{
    public function serialize(mixed $data): string;

    public function deserialize(string $data, string $class): mixed;
}

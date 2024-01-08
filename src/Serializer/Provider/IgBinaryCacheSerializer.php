<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;

final class IgBinaryCacheSerializer implements CacheSerializerInterface
{
    public function serialize(mixed $data): string
    {
        return igbinary_serialize($data);
    }

    public function deserialize(string $data, string $class): mixed
    {
        return igbinary_unserialize($data);
    }
}

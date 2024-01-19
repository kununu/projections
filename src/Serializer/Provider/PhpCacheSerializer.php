<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;

final class PhpCacheSerializer implements CacheSerializerInterface
{
    public function serialize(mixed $data): string
    {
        return serialize($data);
    }

    public function deserialize(string $data, string $class): mixed
    {
        return unserialize($data, ['allowed_classes' => [$class]]);
    }
}

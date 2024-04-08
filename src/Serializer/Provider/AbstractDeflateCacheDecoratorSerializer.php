<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;

abstract class AbstractDeflateCacheDecoratorSerializer implements CacheSerializerInterface
{
    public function __construct(private readonly CacheSerializerInterface $serializer)
    {
    }

    public function serialize(mixed $data): string
    {
        return gzdeflate($this->serializer->serialize($data));
    }

    public function deserialize(string $data, string $class): mixed
    {
        return $this->serializer->deserialize(gzinflate($data), $class);
    }
}

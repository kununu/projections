<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use JMS\Serializer\SerializerInterface;
use Kununu\Projections\Serializer\CacheSerializerInterface;

final class JMSCacheSerializer implements CacheSerializerInterface
{
    private const SERIALIZER_FORMAT = 'json';

    public function __construct(private readonly SerializerInterface $serializer)
    {
    }

    public function serialize(mixed $data): string
    {
        return $this->serializer->serialize($data, self::SERIALIZER_FORMAT);
    }

    public function deserialize(string $data, string $class): mixed
    {
        return $this->serializer->deserialize($data, $class, self::SERIALIZER_FORMAT);
    }
}

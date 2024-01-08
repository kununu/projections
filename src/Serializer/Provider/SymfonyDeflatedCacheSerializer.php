<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use Symfony\Component\Serializer\SerializerInterface;

final class SymfonyDeflatedCacheSerializer extends AbstractDeflateCacheDecoratorSerializer
{
    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct(new SymfonyCacheSerializer($serializer));
    }
}

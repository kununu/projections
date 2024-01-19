<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

use JMS\Serializer\SerializerInterface;

final class JMSDeflatedCacheSerializer extends AbstractDeflateCacheDecoratorSerializer
{
    public function __construct(SerializerInterface $serializer)
    {
        parent::__construct(new JMSCacheSerializer($serializer));
    }
}

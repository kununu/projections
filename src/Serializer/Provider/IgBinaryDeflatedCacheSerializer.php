<?php
declare(strict_types=1);

namespace Kununu\Projections\Serializer\Provider;

final class IgBinaryDeflatedCacheSerializer extends AbstractDeflateCacheDecoratorSerializer
{
    public function __construct()
    {
        parent::__construct(new IgBinaryCacheSerializer());
    }
}

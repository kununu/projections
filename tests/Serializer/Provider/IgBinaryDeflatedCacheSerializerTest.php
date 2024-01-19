<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\IgBinaryDeflatedCacheSerializer;

final class IgBinaryDeflatedCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'igbinary-deflate.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new IgBinaryDeflatedCacheSerializer();
    }
}

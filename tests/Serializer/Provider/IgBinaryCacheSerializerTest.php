<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\IgBinaryCacheSerializer;

final class IgBinaryCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'igbinary.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new IgBinaryCacheSerializer();
    }
}

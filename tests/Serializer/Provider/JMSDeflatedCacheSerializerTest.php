<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use JMS\Serializer\SerializerBuilder;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\JMSDeflatedCacheSerializer;

final class JMSDeflatedCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const string RESULT_FILE = 'jms-deflate.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new JMSDeflatedCacheSerializer(SerializerBuilder::create()->build());
    }
}

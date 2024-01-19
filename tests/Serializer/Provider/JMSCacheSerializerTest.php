<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use JMS\Serializer\SerializerBuilder;
use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\JMSCacheSerializer;

final class JMSCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'jms.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new JMSCacheSerializer(SerializerBuilder::create()->build());
    }
}

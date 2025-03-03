<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\PhpCacheSerializer;

final class PhpCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const string RESULT_FILE = 'php.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new PhpCacheSerializer();
    }
}

<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\PhpDeflatedCacheSerializer;

final class PhpDeflatedCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'php-deflate.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new PhpDeflatedCacheSerializer();
    }
}

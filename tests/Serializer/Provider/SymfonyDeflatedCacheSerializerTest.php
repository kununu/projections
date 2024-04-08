<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\SymfonyDeflatedCacheSerializer;

final class SymfonyDeflatedCacheSerializerTest extends AbstractSymfonySerializerTestCase
{
    protected const RESULT_FILE = 'symfony-deflate.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new SymfonyDeflatedCacheSerializer($this->getSymfonySerializer());
    }
}

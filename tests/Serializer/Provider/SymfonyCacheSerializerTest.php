<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\SymfonyCacheSerializer;

final class SymfonyCacheSerializerTest extends AbstractSymfonySerializerTestCase
{
    protected const string RESULT_FILE = 'symfony.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new SymfonyCacheSerializer($this->getSymfonySerializer());
    }
}

<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\SymfonyCacheSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SymfonyCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'symfony.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new SymfonyCacheSerializer(
            new Serializer(
                [new ArrayDenormalizer(), new PropertyNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            )
        );
    }
}

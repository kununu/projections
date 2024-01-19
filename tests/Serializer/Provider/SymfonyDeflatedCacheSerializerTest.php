<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\Serializer\CacheSerializerInterface;
use Kununu\Projections\Serializer\Provider\SymfonyDeflatedCacheSerializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

final class SymfonyDeflatedCacheSerializerTest extends AbstractCacheSerializerTestCase
{
    protected const RESULT_FILE = 'symfony-deflate.result';

    protected function getSerializer(): CacheSerializerInterface
    {
        return new SymfonyDeflatedCacheSerializer(
            new Serializer(
                [new ArrayDenormalizer(), new PropertyNormalizer(), new ObjectNormalizer()],
                [new JsonEncoder()]
            )
        );
    }
}

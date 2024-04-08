<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class AbstractSymfonySerializerTestCase extends AbstractCacheSerializerTestCase
{
    protected function getSymfonySerializer(): SerializerInterface
    {
        return new Serializer(
            [new ArrayDenormalizer(), new PropertyNormalizer(), new ObjectNormalizer()],
            [new JsonEncoder()]
        );
    }
}

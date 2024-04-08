<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests;

use ArrayIterator;
use BadMethodCallException;
use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionItemIterableTrait;
use Kununu\Projections\Tag\Tags;
use PHPUnit\Framework\TestCase;

final class ProjectionItemIterableTraitTest extends TestCase
{
    public function testTrait(): void
    {
        $validClass = new class() implements ProjectionItemIterableInterface {
            use ProjectionItemIterableTrait;

            public function getKey(): string
            {
                return '';
            }

            public function getTags(): Tags
            {
                return new Tags();
            }
        };

        $validClass->storeData([1, 2, 3]);

        self::assertEquals([1, 2, 3], $validClass->data());

        $iterator = new ArrayIterator();
        $iterator->append('a');
        $iterator->append('b');
        $iterator->append(5);
        $validClass->storeData($iterator);

        self::assertEquals(['a', 'b', 5], $validClass->data());

        $invalidClass = new class() {
            use ProjectionItemIterableTrait;
        };

        $this->expectException(BadMethodCallException::class);
        $invalidClass->storeData([1, 2]);
    }
}

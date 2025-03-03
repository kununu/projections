<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Tag;

use Kununu\Projections\Tag\Tag;
use PHPUnit\Framework\TestCase;

final class TagTest extends TestCase
{
    public function testThatTagCanBeCreated(): void
    {
        $tag = new Tag('tag');

        self::assertEquals('tag', $tag->tag);
    }

    public function testThatTagCanBeTreatedAsString(): void
    {
        $tag = new Tag('tag');

        self::assertEquals('tag', (string) $tag);
    }

    public function testThatWhenTwoTagsHaveTheSameValueThenTheyAreEqual(): void
    {
        $tag1 = new Tag('tag');
        $tag2 = new Tag('tag');

        self::assertTrue($tag1->equals($tag2));
        self::assertTrue($tag2->equals($tag1));
    }

    public function testThatWhenTwoTagsHaveDistinctValuesThenTheyAreDifferent(): void
    {
        $tag1 = new Tag('tag_1');
        $tag2 = new Tag('tag_2');

        self::assertFalse($tag1->equals($tag2));
        self::assertFalse($tag2->equals($tag1));
    }
}

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

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertEquals('tag', $tag->value());
    }

    public function testThatTagCanBeTreatedAsString(): void
    {
        $tag = new Tag('tag');

        $this->assertEquals('tag', (string) $tag);
    }

    public function testThatWhenTwoTagsHaveTheSameValueThenTheyAreEqual(): void
    {
        $tag1 = new Tag('tag');
        $tag2 = new Tag('tag');

        $this->assertTrue($tag2->equals($tag1));
    }
}

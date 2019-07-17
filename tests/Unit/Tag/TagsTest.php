<?php declare(strict_types=1);

namespace Kununu\ProjectionsTests\Unit\Tag;

use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;
use PHPUnit\Framework\TestCase;

final class TagsTest extends TestCase
{
    public function testThatCanBeCreatedWithoutTags(): void
    {
        $tags = new Tags();

        $this->assertCount(0, $tags->raw());
    }

    public function testThatCanBeCreatedWithTags(): void
    {
        $tags = new Tags(new Tag('tag_1'), new Tag('tag_2'));

        $this->assertCount(2, $tags->raw());
    }

    public function testUniquenessOfTags(): void
    {
        $tags = new Tags(
            new Tag('tag_1'),
            new Tag('tag_2'),
            new Tag('tag_1'),
            new Tag('tag_3'),
            new Tag('tag_2')
        );

        $this->assertCount(3, $tags->raw());
    }

    public function testThatRawReturnsAllTagsAsAnArray(): void
    {
        $tags = new Tags(new Tag('tag_1'), new Tag('tag_2'), new Tag('tag_3'));

        $this->assertEquals(
            ['tag_1', 'tag_2', 'tag_3'],
            $tags->raw()
        );
    }
}

<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Tag;

use Kununu\Projections\Tag\ProjectionTagGenerator;
use Kununu\Projections\Tag\Tags;
use PHPUnit\Framework\TestCase;

final class ProjectionTagGeneratorTest extends TestCase
{
    use ProjectionTagGenerator;

    public function testCreateTagsFromArray(): void
    {
        $tags = $this::createTagsFromArray('tag_1', 'tag_2');

        $this->assertInstanceOf(Tags::class, $tags);
        $this->assertEquals(['tag_1', 'tag_2'], $tags->raw());
    }
}

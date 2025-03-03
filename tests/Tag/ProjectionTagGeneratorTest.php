<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Tag;

use Kununu\Projections\Tag\ProjectionTagGenerator;
use PHPUnit\Framework\TestCase;

final class ProjectionTagGeneratorTest extends TestCase
{
    use ProjectionTagGenerator;

    public function testCreateTagsFromArray(): void
    {
        $tags = $this::createTagsFromArray('tag_1', 'tag_2', 'tag_1');

        self::assertEquals(['tag_1', 'tag_2'], $tags->raw());
    }
}

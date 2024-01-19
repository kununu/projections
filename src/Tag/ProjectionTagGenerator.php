<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

trait ProjectionTagGenerator
{
    private static function createTagsFromArray(string ...$tags): Tags
    {
        return new Tags(...array_map(static fn(string $tag): Tag => new Tag($tag), $tags));
    }
}

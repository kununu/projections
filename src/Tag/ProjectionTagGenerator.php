<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

trait ProjectionTagGenerator
{
    private static function createTagsFromArray(string ...$tags): Tags
    {
        return new Tags(
            ...array_map(
                function(string $tag): Tag {
                    return new Tag($tag);
                },
                $tags
            )
        );
    }
}

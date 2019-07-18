<?php declare(strict_types=1);

namespace Kununu\Projections\Tag;

trait ProjectionTagGenerator
{
    private static function createTagsFromArray(string ...$tagsAsStrings): Tags
    {
        $tags = [];

        foreach ($tagsAsStrings as $tag) {
            $tags[] = new Tag($tag);
        }

        return new Tags(...$tags);
    }
}

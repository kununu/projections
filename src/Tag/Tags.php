<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

final class Tags
{
    private readonly array $tags;

    public function __construct(Tag ...$tags)
    {
        $this->tags = $this->createTags(...$tags);
    }

    public function raw(): array
    {
        return array_keys($this->tags);
    }

    private function createTags(Tag ...$tags): array
    {
        $values = [];
        foreach ($tags as $tag) {
            if (isset($values[$tag->tag])) {
                continue;
            }
            $values[$tag->tag] = true;
        }

        return $values;
    }
}

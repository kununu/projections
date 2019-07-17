<?php declare(strict_types=1);

namespace Kununu\Projections\Tag;

final class Tags
{
    private $tags = [];
    private $tagsAsStrings = [];

    public function __construct(Tag ...$tags)
    {
        foreach ($tags as $tag) {
            $this->add($tag);
        }
    }

    public function raw(): array
    {
        return array_keys($this->tagsAsStrings);
    }

    private function add(Tag $tag): void
    {
        $value = $tag->value();

        if (array_key_exists($value, $this->tagsAsStrings)) {
            return;
        }

        $this->tags[] = $tag;
        $this->tagsAsStrings[$value] = true;
    }
}

<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

final class Tags
{
    private array $tags = [];

    public function __construct(Tag ...$tags)
    {
        foreach ($tags as $tag) {
            $this->add($tag);
        }
    }

    public function raw(): array
    {
        return array_keys($this->tags);
    }

    private function add(Tag $tag): void
    {
        $value = $tag->value();

        if (isset($this->tags[$value])) {
            return;
        }

        $this->tags[$value] = true;
    }
}

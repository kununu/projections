<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

use Stringable;

final readonly class Tag implements Stringable
{
    public function __construct(public string $tag)
    {
    }

    public function __toString(): string
    {
        return $this->tag;
    }

    public function equals(Tag $other): bool
    {
        return $other->tag === $this->tag;
    }
}

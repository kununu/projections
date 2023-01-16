<?php
declare(strict_types=1);

namespace Kununu\Projections\Tag;

final class Tag
{
    private $tag;

    public function __construct(string $tag)
    {
        $this->tag = $tag;
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public function value(): string
    {
        return $this->tag;
    }

    public function equals(Tag $other): bool
    {
        return $other->value() === $this->value();
    }
}

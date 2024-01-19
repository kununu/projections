<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\ProjectionItem;

use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class ProjectionItemStub implements ProjectionItemInterface
{
    private const PROJECTION_KEY = 'test_%s';

    public function __construct(private string $id)
    {
    }

    public function getKey(): string
    {
        return sprintf(self::PROJECTION_KEY, $this->id);
    }

    public function getTags(): Tags
    {
        return new Tags(new Tag('test'), new Tag('kununu'), new Tag($this->id));
    }
}

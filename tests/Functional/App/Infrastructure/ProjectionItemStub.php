<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Functional\App\Infrastructure;

use Kununu\Projections\ProjectionItem;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

class ProjectionItemStub implements ProjectionItem
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getKey(): string
    {
        return sprintf('projection_item_stub_%s', $this->id);
    }

    public function getTags(): Tags
    {
        return new Tags(new Tag('stub'), new Tag($this->id));
    }
}

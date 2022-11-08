<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Functional\App\Repository;

use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class ProjectionItemStub implements ProjectionItemInterface
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

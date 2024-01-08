<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Serializer\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionItemIterableTrait;
use Kununu\Projections\Tag\ProjectionTagGenerator;
use Kununu\Projections\Tag\Tags;

final class MyItemStub implements ProjectionItemIterableInterface
{
    use ProjectionItemIterableTrait;
    use ProjectionTagGenerator;

    public function __construct(private int $id, private string $name)
    {
    }

    public function getKey(): string
    {
        return sprintf('item_stub_%d', $this->id);
    }

    public function getTags(): Tags
    {
        return self::createTagsFromArray('a_tag');
    }
}

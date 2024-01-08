<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Provider;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionItemIterableTrait;
use Kununu\Projections\Tag\ProjectionTagGenerator;
use Kununu\Projections\Tag\Tags;

final class MyStubProjectionItem implements ProjectionItemIterableInterface
{
    use ProjectionItemIterableTrait;
    use ProjectionTagGenerator;

    public function __construct(private int $id)
    {
    }

    public function getKey(): string
    {
        return sprintf('my_data_%d', $this->id);
    }

    public function getTags(): Tags
    {
        return self::createTagsFromArray('my_tag');
    }
}

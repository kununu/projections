<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Stubs\ProjectionItem;

use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionItemIterableTrait;
use Kununu\Projections\Tag\ProjectionTagGenerator;
use Kununu\Projections\Tag\Tags;

final class ProjectionItemIterableStub implements ProjectionItemIterableInterface
{
    use ProjectionItemIterableTrait;
    use ProjectionTagGenerator;

    private const string PROJECTION_KEY = 'test_iterable_%s';

    public function __construct(public readonly string $id, public readonly ?string $stuff = null)
    {
    }

    public function getKey(): string
    {
        return sprintf(self::PROJECTION_KEY, $this->id);
    }

    public function getTags(): Tags
    {
        return self::createTagsFromArray('test', 'kununu', $this->id);
    }
}

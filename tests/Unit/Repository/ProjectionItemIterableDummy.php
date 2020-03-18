<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Repository;

use Kununu\Projections\{
    ProjectionItem,
    ProjectionItemIterable,
    ProjectionItemIterableTrait,
    Tag\Tag,
    Tag\Tags};

final class ProjectionItemIterableDummy implements ProjectionItemIterable
{
    use ProjectionItemIterableTrait;

    private const PROJECTION_KEY = 'test_item_iterable_%s';

    protected $id;

    protected $stuff;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getKey(): string
    {
        return sprintf(self::PROJECTION_KEY, $this->id);
    }

    public function getStuff(): string
    {
        return $this->stuff;
    }

    public function setStuff(string $stuff): ProjectionItem
    {
        $this->stuff = $stuff;

        return $this;
    }

    public function getTags(): Tags
    {
        return new Tags(new Tag('test'), new Tag('kununu'), new Tag($this->id));
    }
}

<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Repository;

use Kununu\Projections\{
    AbstractProjectionItemIterable,
    ProjectionItem,
    Tag\Tag,
    Tag\Tags};

final class ProjectionItemArrayDataDummy extends AbstractProjectionItemIterable
{
    private const PROJECTION_KEY = 'test_item_array_data_%s';

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

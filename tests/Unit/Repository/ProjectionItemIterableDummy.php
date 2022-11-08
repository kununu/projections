<?php
declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Repository;

use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\ProjectionItemIterableInterface;
use Kununu\Projections\ProjectionItemIterableTrait;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class ProjectionItemIterableDummy implements ProjectionItemIterableInterface
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

    public function setStuff(string $stuff): ProjectionItemInterface
    {
        $this->stuff = $stuff;

        return $this;
    }

    public function getTags(): Tags
    {
        return new Tags(new Tag('test'), new Tag('kununu'), new Tag($this->id));
    }
}

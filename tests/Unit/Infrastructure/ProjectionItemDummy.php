<?php declare(strict_types=1);

namespace Kununu\Projections\Tests\Unit\Infrastructure;

use Kununu\Projections\ProjectionItem;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class ProjectionItemDummy implements ProjectionItem
{
    const PROJECTION_KEY = 'test_%s';

    const STUFF_PROJECTED_KEY = 'stuff';

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

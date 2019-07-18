<?php declare(strict_types=1);

namespace Kununu\Projections;

use Kununu\Projections\Tag\Tags;

interface ProjectionRepository
{
    public function add(ProjectionItem $item): void;

    public function addDeferred(ProjectionItem $item): void;

    public function flush(): void;

    public function get(ProjectionItem $item): ?ProjectionItem;

    public function delete(ProjectionItem $item): void;

    public function deleteByTags(Tags $tags): void;
}

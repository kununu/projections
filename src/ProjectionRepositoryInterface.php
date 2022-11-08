<?php
declare(strict_types=1);

namespace Kununu\Projections;

use Kununu\Projections\Tag\Tags;

interface ProjectionRepositoryInterface
{
    public function add(ProjectionItemInterface $item): void;

    public function addDeferred(ProjectionItemInterface $item): void;

    public function flush(): void;

    public function get(ProjectionItemInterface $item): ?ProjectionItemInterface;

    public function delete(ProjectionItemInterface $item): void;

    public function deleteByTags(Tags $tags): void;
}

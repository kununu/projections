<?php declare(strict_types=1);

namespace Kununu\Projections;

use Kununu\Projections\Tag\Tags;

interface ProjectionItem
{
    public function getKey(): string;

    public function getTags(): Tags;
}

<?php declare(strict_types=1);

namespace Kununu\Projections;

interface ProjectionItemIterable extends ProjectionItem
{
    public function storeData(iterable $data): ProjectionItemIterable;

    public function data(): iterable;
}

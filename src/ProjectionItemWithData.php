<?php declare(strict_types=1);

namespace Kununu\Projections;

interface ProjectionItemWithData extends ProjectionItem
{
    public function storeData(iterable $data): ProjectionItemWithData;

    public function data(): iterable;
}

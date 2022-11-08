<?php
declare(strict_types=1);

namespace Kununu\Projections;

interface ProjectionItemIterableInterface extends ProjectionItemInterface
{
    public function storeData(iterable $data): ProjectionItemIterableInterface;

    public function data(): iterable;
}

<?php declare(strict_types=1);

namespace Kununu\Projections;

abstract class AbstractProjectionItemWithData implements ProjectionItemWithData
{
    private $data;

    public function storeData(iterable $data): ProjectionItemWithData
    {
        $this->data = $data;

        return $this;
    }

    public function data(): iterable
    {
        return $this->data;
    }
}

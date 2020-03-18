<?php declare(strict_types=1);

namespace Kununu\Projections;

abstract class AbstractProjectionItemIterable implements ProjectionItemIterable
{
    private $data;

    public function storeData(iterable $data): ProjectionItemIterable
    {
        $this->data = $data;

        return $this;
    }

    public function data(): iterable
    {
        return $this->data;
    }
}

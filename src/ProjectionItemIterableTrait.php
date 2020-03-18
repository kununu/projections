<?php declare(strict_types=1);

namespace Kununu\Projections;

use BadMethodCallException;

trait ProjectionItemIterableTrait
{
    /** @var array */
    private $data;

    public function storeData(iterable $data): ProjectionItemIterable
    {
        if ($this instanceof ProjectionItemIterable) {
            if (is_array($data)) {
                $this->data = $data;
            } else {
                $values = [];
                array_push($values, ...$data);
                $this->data = $values;
            }

            return $this;
        }

        throw new BadMethodCallException(
            sprintf('Class using this trait must be a %s', ProjectionItemIterable::class)
        );
    }

    public function data(): iterable
    {
        return $this->data;
    }
}

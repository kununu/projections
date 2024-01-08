<?php
declare(strict_types=1);

namespace Kununu\Projections;

use BadMethodCallException;
use JMS\Serializer\Annotation\Type;

trait ProjectionItemIterableTrait
{
    #[Type('array')]
    private array $data;

    public function storeData(iterable $data): ProjectionItemIterableInterface
    {
        if (!is_a($this, ProjectionItemIterableInterface::class)) {
            throw new BadMethodCallException(sprintf('Class using this trait must be a %s', ProjectionItemIterableInterface::class));
        }

        if (is_array($data)) {
            $this->data = $data;
        } else {
            $values = [];
            array_push($values, ...$data);
            $this->data = $values;
        }

        return $this;
    }

    public function data(): iterable
    {
        return $this->data;
    }
}

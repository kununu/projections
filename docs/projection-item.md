# Projection Item

## ProjectionItemInterface

A projection is represented by an object that implements `ProjectionItemInterface` interface. This object is called **projection item** and holds on its properties the data to be projected.

```php
interface ProjectionItemInterface
{
    public function getKey(): string;

    public function getTags(): Tags;
}
```

Here's an example of projection item:

```php
<?php
declare(strict_types=1);

namespace Kununu\Example;

use Kununu\Projections\ProjectionItemInterface;
use Kununu\Projections\Tag\ProjectionTagGenerator;

final class ExampleProjectionItem implements ProjectionItemInterface
{
    use ProjectionTagGenerator;

    public function __construct(private readonly string $id, private readonly string $someValue)
    {
    }

    public function getKey(): string
    {
        return sprintf('example_projection_item_%s', $this->id);
    }

    public function getTags(): Tags
    {
        // This is in ProjectionTagGenerator trait
        return ProjectionTagGenerator::createTagsFromArray('example_tag', $this->id);
        // It is functional equivalent to:
        //
        // return new Tags(new Tag('example_tag'), new Tag($this->id));
    }
}
```

The following methods must be implemented:

* `getKey()` returns the unique identifier of the projection. If projections are stored with the same key then they will be overridden.

* `getTags()` returns the tags marked on the projection item. These can be used later for bulk operations on projections, like delete all projections with a certain tag.

## ProjectionItemIterableInterface

The package also offers an extension of `ProjectionItemInterface` designed to store generic data (the data itself will be any PHP iterable, like an array).

The interface is `ProjectionItemIterableInterface` and must implement the following methods:

```php
interface ProjectionItemIterableInterface extends ProjectionItemInterface
{
    public function storeData(iterable $data): ProjectionItemIterableInterface;

    public function data(): iterable;
}
```

A trait called `ProjectionItemIterableTrait` is provided with those methods already implemented and with the data stored as an array, so just use it your projection item classes, and you're good to go.

Just bear in mind that the trait is only implementing the methods defined in `ProjectionItemIterableInterface` and not those of `ProjectionItemInterface` so it is still responsibility of your projection item class to implement them!

---

[Back to Index](../README.md)

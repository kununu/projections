# Repository

## ProjectionRepositoryInterface

The [projection item](projection-item.md) is projected through a repository which implements `ProjectionRepositoryInterface` interface.

This holds methods to get, add and delete the projections. The methods are used by passing a **projection item** object.

```php
interface ProjectionRepositoryInterface
{
    public function add(ProjectionItemInterface $item): void;

    public function addDeferred(ProjectionItemInterface $item): void;

    public function flush(): void;

    public function get(ProjectionItemInterface $item): ?ProjectionItemInterface;

    public function delete(ProjectionItemInterface $item): void;

    public function deleteByTags(Tags $tags): void;
}
```

* `add()` method immediately projects the item.
* `addDeferred()` method sets items to be projected, but they are only projected when `flush()` is called.
* `get()` method gets a projected item. If it is not projected, then `null` is returned.
* `delete()` method deletes item from projection
* `deleteByTags()` method deletes all projected items that have at least one of the tags passed as argument

## Implementations

### SymfonyCacheProjectionRepository

This class implements a repository, which projects the items using [Symfony's Tag Aware Cache Pool component](https://github.com/symfony/symfony/blob/5.4/src/Symfony/Contracts/Cache/TagAwareCacheInterface.php).

This means that then when projecting an [item](docs/projection-item.md), if tags are available for it, the item will also include tags and those items can be invalidated in the cache with the `deleteByTags` method.  

### Psr6CacheProjectionRepository

This class implements a repository, which projects the items using any [PSR-6 Cache implementation](https://www.php-fig.org/psr/psr-6/).

Unlike the `SymfonyCacheProjectionRepository`, no support for tags is available (they will be ignored when projecting) and calling `deleteByTags` method will throw an exception.

If your PSR-6 implementation supports tags then is up to you to create an implementation of `ProjectionRepositoryInterface` and make the required changes.

You expand `AbstractProjectionRepository` and do the required changes to `deleteByTags` and `createCacheItem` methods. See `SymfonyCacheProjectionRepository` which does just that. 

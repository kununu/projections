# Cache Cleaner

## CacheCleanerInterface

Sometimes we need to force the cleaning of caches. In order to do this the library offers an interface called `CacheCleanerInterface`:

```php
interface CacheCleanerInterface
{
    public function clear(): void;
}
```

It only has one method called `clear` which should as the name says clear the data on the cache.

## AbstractCacheCleanerByTags

The interface `CacheCleanerInterface` by itself is not really useful. One of the most common cases when cleaning/invalidating caches is to delete a series of data.

The `AbstractCacheCleanerByTags` provides a base class that will allow you to invalidate cache items by **Tags**.

As we already have seen, the `ProjectionRepositoryInterface` already has a method called `deleteByTags`, so this class will combine that usage and abstract it.

So your cache cleaner class by tags should be instantiated with a `ProjectionRepositoryInterface` instance (and also with a PSR logger instance), and simply implement the `getTags` method which must return the `Tags` collection that will be passed to the `deleteByTags` on the repository instance.

*Note*: Your implementation of `ProjectionRepositoryInterface` **must be compatible with tags**, otherwise this will fail!

```php
public function __construct(
    private readonly ProjectionRepositoryInterface $projectionRepository,
    private readonly LoggerInterface $logger,
    private readonly string $logLevel = LogLevel::INFO
);

abstract protected function getTags(): Tags;
```

Example:

```php
<?php
declare(strict_types=1);

namespace Kununu\Example;

use Kununu\Projections\CacheCleaner\CacheCleanerInterface;
use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;

final class MyCacheCleaner extends AbstractCacheCleanerByTags
{
    protected function getTags(): Tags
    {
        return new Tags(
            new Tag('my-tag1'),
            new Tag('my-tag2')
        );
    }
};

final class MyClass
{
    public function __construct(private readonly CacheCleanerInterface $cacheCleaner)
    {
    }

    public function myMethod(mixed ...$myArguments): void
    {
        $this->cacheCleaner->clear();
    }
}

$cacheCleaner = new MyCacheCleaner($myProjectionRepo, $myLogger);
$myClass = new MyClass($cacheCleaner);

// When I call `myMethod` it will call `MyCacheCleaner` and delete all cache entries that
// are tagged with 'my-tag1' and 'my-tag2'
$myClass->myMethod();
```

### AbstractCacheCleanerTestCase

In order to help you unit testing your cache cleaners implementations the `AbstractCacheCleanerTestCase` exists for that purpose.

Just make you test class extend it and override the `TAGS` constant and implement the `getCacheCleaner` method.

Example:

```php
<?php
declare(strict_types=1);

namespace Kununu\Example;

use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\TestCase\CacheCleaner\AbstractCacheCleanerTestCase;
use Psr\Log\LoggerInterface;

final class MyCacheCleanerTest extends AbstractCacheCleanerTestCase
{
    protected const TAGS = ['my-tag1', 'my-tag2'];

    protected function getCacheCleaner(
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ): CacheCleanerInterface {
        // Return a new instance of your cache cleaner implementation
        return new MyCacheCleaner($projectionRepository, $logger); 
    }
}
```

The `TAGS` constant must be the tags that you expect that your cache cleaner class will use.

For the example above we are expecting that `MyCacheCleaner::getTags` will return a `Tags` collection with the same tags defined in the constant, e.g.:

```php
<?php
declare(strict_types=1);

namespace Kununu\Example

use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;

final class MyCacheCleaner extends AbstractCacheCleanerByTags
{
    protected function getTags(): Tags
    {
        return new Tags(new Tag('my-tag1'), new Tag('my-tag2'));
    }
}
```

## CacheCleanerChain

What if you want to clear more than one cache/more than one set of tags? Easy, you create a `CacheCleanerChain`.

This class is constructed by passing the desired instances of your classes that implement the `CacheCleanerInterface` interface (which could be subclasses of `AbstractCacheCleanerByTags`) and then (as itself also implements the `CacheCleanerInterface` interface) just call the `clear` method.

Example:

```php
<?php
declare(strict_types=1);

namespace Kununu\Example

use Kununu\Projections\CacheCleaner\CacheCleanerInterface;
use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;

// Continuing our example, let's add more cache cleaners...
final class MySecondCacheCleaner extends AbstractCacheCleanerByTags
{
    protected function getTags(): Tags
    {
        return new Tags(new Tag('my-tag3'));
    }
};

final class MyThirdCacheCleaner implements CacheCleanerInterface
{
    public function clear(): void
    {
        // Here I am deleting my cache by using some other process...
    }
}

$cacheCleaner1 = new MyCacheCleaner($myProjectionRepo, $myLogger);
$cacheCleaner2 = new MySecondCacheCleaner($myProjectionRepo, $myLogger);
$cacheCleaner3 = new MyThirdCacheCleaner();

$cacheCleaner = new CacheCleanerChain(
    $cacheCleaner1,
    $cacheCleaner2,
    $cacheCleaner3
);

$myClass = new MyClass($cacheCleaner);

// When I call `myMethod` it will clear all the caches as defined on each cleaner injected into the chain $cacheCleaner
$myClass->myMethod();
```

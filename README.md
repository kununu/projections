# Kununu Projections

Projections are a temporary storage and are a way to access data faster than fetching it from a regular storage.

Data needs to be projected first, so then it's projection can be accessed, without the need to access the actual source of truth, which is usually a slower process.

Projections have a short lifetime, and are not updated automatically if data in source of truth changes. So they need to be frequently refreshed.

## Overview

This repository contains the interfaces to implement projections logic.

It also includes an implementation of the projection over the Symfony's Tag Aware Cache Pool component, which can use several cache providers, like Memcached, Redis or simply process memory, amongst others.

## Installation

### Add this repository to your project composer.json. Copy the following configuration

```json
"repositories": [
  ...
  {
    "type": "vcs",
    "url": "https://github.com/kununu/projections.git",
    "no-api": true
  }
]
```

### Require this library to your project

```bash
composer require kununu/projections
```

### If you wish to have projections implemented via Symfony's Tag Aware Cache Pool, you must also request the required packages for that implementation
 
```bash
composer require symfony/cache
composer require jms/serializer
```

If you want to use this library on a Symfony App you may want to require the `jms/serializer-bundle` instead of `jms/serializer`

```bash
composer require jms/serializer-bundle
```

## Usage

### `ProjectionItem`
 
A projection is represented by an object that implements `ProjectionItem` interface. This object is called **projection item** and holds on its properties the data to be projected.

Here's an example of projection item:

```php
namespace Kununu\Example;

class ExampleProjectionItem implements ProjectionItem
{
    private $id;
    private $someValue;

    public function __construct(string $id, string $someValue)
    {
        $this->id        = $id;
        $this->someValue = $someValue;
    }

    public function getKey(): string
    {
        return sprintf('example_projection_item_%s', $this->id);
    }

    public function getTags(): Tags
    {
        return new Tags(new Tag('example_tag'), new Tag($this->id));
    }
}
```

The `getKey()` and `getTags()` methods must be implemented.
 
The `getKey()` method is the unique identifier of the projection. If projections are stored with the same key then they will be overridden.
 
The `getTags()` method serves to mark the projection item with a tag. These can be used later for bulk operations on projections, like delete all projections with a certain tag.
    
### `ProjectionItemIterable`

The package also offers an extension of `ProjectionItem` designed to store generic data (the data itself will be any PHP iterable, like an array).

The interface is `ProjectionItemIterable` and must implement the following methods:

```php
 interface ProjectionItemIterable extends ProjectionItem
 {
     public function storeData(iterable $data): ProjectionItemArrayData;

     public function data(): iterable;
 }
```

A trait called `ProjectionItemIterableTrait` is provided with those methods already implemented and with the data stored as an array, so just use it your projection item classes and you're good to go.

Just bear in mind that the trait is only implementing the methods defined in `ProjectionItemIterable` and not those of `ProjectionItem` so it is still responsibility of your projection item class to implement them!

### `ProjectionRepository`

The **projection item** is projected through a repository which implements `ProjectionRepository` interface.

This holds methods to get, add and delete the projections. The methods are used by passing a **projection item** object.

 ```php
 interface ProjectionRepository
 {
     public function add(ProjectionItem $item): void;

     public function addDeferred(ProjectionItem $item): void;

     public function flush(): void;

     public function get(ProjectionItem $item): ?ProjectionItem;

     public function delete(ProjectionItem $item): void;

     public function deleteByTags(Tags $tags): void;
 }
 ```

 * `add()` method immediately projects the item.
 * `addDeferred()` method sets items to be projected, but they are only projected when `flush()` is called.
 * `get()` method gets a projected item. If it not projected, then `null` is returned.
 * `delete()` method deletes item from projection
 * `deleteByTags()` method deletes all projected items that have at least one of the tags passed as argument

Right now there is only an implementation of a repository, which projects the items using Symfony's Tag Aware Cache Pool component.

This repository is called `CachePoolProjectionRepository`.

### Using `CachePoolProjectionRepository`

#### Serialization

Besides the Symfony's Tag Aware Cache Pool interface, this repository uses the JMS Serializer. The following snippet is the repository's constructor:

```php
    public function __construct(TagAwareAdapterInterface $cachePool, SerializerInterface $serializer)
    {
        $this->cachePool  = $cachePool;
        $this->serializer = $serializer;
    }
```
    
So there is the need to define the serialization config for the Projection items. For instance, for the previous `ExampleProjectionItem` example, here is an example of the JMS Serializer XML config for this class:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<serializer>
  <class name="Kununu\Example\ExampleProjectionItem">
    <property name="id" type="string"></property>
    <property name="someValue" type="string"></property>
  </class>
 </serializer>
```

This should be saved in a `ExampleProjectionItem.xml` file.
    
The data that you want projected needs exist on the serializer config in order to be actually projected. In this example you can see that the two properties of the projection item are on the config.
    
This configuration needs to be loaded into the JMS Serializer and the repository needs to be instantiated in order to be used.

#### Usage with Symfony >= 4.0

Create a cache pool with Symfony config. Here's an example for the cache pool to use Memcached:

```yaml
framework:
    cache:
        prefix_seed: "example"
        default_memcached_provider: "memcached://172.0.0.1:1121"
        pools:
            example.cache.projections:
                adapter: cache.adapter.memcached
                default_lifetime: 3600
    
```

This automatically creates a `example.cache.projections` service. In this case the lifetime for the projections is 3600 seconds = 1 hour.
    
Here is assumed that the `jms/serializer-bundle` was required. The minimum configuration you need for the JMS Serializer Bundle is:

```yaml
jms_serializer:
    metadata:
        directories:
            projections:
                namespace_prefix: "Kununu\Example"
                path: "%kernel.root_dir%/Repository/Resources/config/serializer"
```
    
where `%kernel.root_dir%/Repository/Resources/config/serializer` is the directory where is the JMS Serializer configuration files for the projection items, which means the previous `ExampleProjectionItem.xml` file is inside.
    
Please notice that the namespace prefix of the projection item class is also defined in here.
    
Next define the `CachePoolProjectionRepository` as a Symfony service:

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    Kununu/Projections/Repository/CachePoolProjectionRepository:
        class: Kununu\Projections\Repository\CachePoolProjectionRepository
        arguments:
            - '@example.cache.projections'
            - '@jms_serializer'

    example.cache.projections.tagged:
        class: Symfony\Component\Cache\Adapter\TagAwareAdapter
        decorates: 'example.cache.projections'
```

Note that the `TagAwareAdapter` is added as a decorator for the cache pool service.
    
Now you can inject the repository's service. Example:

```yaml
App\Infrastructure\UseCase\Query\GetProfileCommonByUuid\DataProvider\ProjectionDataProvider:
    arguments:
        - '@Kununu/Projections/Repository/CachePoolProjectionRepository'
```
    
And inside the respective class we should depend only on the `ProjectionRepository` interface.

```php
class ProjectionDataProvider
{
    private $projectionRepository;

    public function __construct(ProjectionRepository $projectionRepository)
    {
        $this->projectionRepository = $projectionRepository;
    }

    ...
}
```
    
Now we can start reading, setting and deleting from the cache pool :)


### `CacheCleaner`

Sometimes we need to force the cleaning of caches. In order to do this the library offers an interface called `CacheCleaner`:

```php
interface CacheCleaner
{
    public function clear(): void;
}
```

It only has one method called `clear` which should as the name says clear the data on the cache.

#### AbstractCacheCleanerByTags

The interface `CacheCleaner` by itself is not really useful. One of the most common cases when cleaning/invalidating caches is to delete a series of data.

The `AbstractCacheCleanerByTags` provides a base class that will allow you to invalidate cache items by **Tags**.

As we already have seen, the `ProjectionRepository` already has a method called `deleteByTags`, so this class will combine that usage and abstract it.

So your cache cleaner class by tags should be instantiated with a `ProjectionRepository` instance (and also with a PSR logger instance), and simply implement the `getTags` method which must return the `Tags` collection that will be passed to the `deleteByTags` on the repository instance.


```php
public function __construct(ProjectionRepository $projectionRepository, LoggerInterface $logger);


abstract protected function getTags(): Tags;
```

Example:

```php

use Kununu\Projections\CacheCleaner\CacheCleaner;
use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;

class MyCacheCleaner extends AbstractCacheCleanerByTags
{
    protected function getTags(): Tags
    {
        return new Tags(
            new Tag('my-tag1'),
            new Tag('my-tag2')
        );
    }
};

class MyClass
{
    private $cacheCleaner;

    public function __construct(CacheCleaner $cacheCleaner)
    {
        $this->cacheCleaner = $cacheCleaner;
    }


    public function myMethod(...$myArguments): void
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

### `CacheCleanerChain`

What if you want to clear more than one cache/more than one set of tags? Easy, you create a `CacheCleanerChain`.

This class is constructed by passing the desired instances of your classes that implement the `CacheCleaner` interface (which could be sub-classes of `AbstractCacheCleanerByTags`) and then (as itself also implements the `CacheCleaner` interface) just call the `clear` method.

Example:

```php

use Kununu\Projections\CacheCleaner\CacheCleaner;
use Kununu\Projections\CacheCleaner\AbstractCacheCleanerByTags;


// Continuing our example, let's add more cache cleaners...

class MySecondCacheCleaner extends AbstractCacheCleanerByTags
{
    protected function getTags(): Tags
    {
        return new Tags(new Tag('my-tag3'));
    }
};


class MyThirdCacheCleaner implements CacheCleaner
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

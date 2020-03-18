# Kununu Projections

Projections are a temporary storage and are a way to access data faster than fetching it from a regular storage.

Data needs to be projected first, so then it's projection can be accessed, without the need to access the actual source of truth, which is usually a slower process.

Projections have a short lifetime, and are not updated automatically if data in source of truth changes. So they need to be frequently refreshed.

## Overview

This repository contains the interfaces to implement projections logic.

It also includes an implementation of the projection over the Symfony's Tag Aware Cache Pool component, which can use several cache providers, like Memcached, Redis or simply process memory, amongst others.

## Installation

1. Add this repository to your project composer.json. Copy the following configuration
    ```
      "repositories": [
        ...
        {
          "type": "vcs",
          "url": "https://github.com/kununu/projections.git",
          "no-api": true
        }
      ]
    ```

2. Require this library to your project
    ```
    composer require kununu/projections
    ```

3. If you wish to have projections implemented via Symfony's Tag Aware Cache Pool, you must also request the required packages for that implementation
    ```
    composer require symfony/cache
    composer require jms/serializer
    ```
    If you want to use this library on a Symfony App you may want to require the `jms/serializer-bundle` instead of `jms/serializer`
    ```
    composer require jms/serializer-bundle
    ```

## Usage

1. A projection is represented by an object that implements `ProjectionItem` interface. This object is called **projection item** and holds on its properties the data to be projected.

    Here's an example of projection item:

    ```
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
    
2. The package also offers an extension of `ProjectionItem` designed to store generic data (the data itself will be any PHP iterable, like an array).

	The interface is `ProjectionItemIterable` and must implement the following methods:

    ```
    interface ProjectionItemIterable extends ProjectionItem
    {
        public function storeData(iterable $data): ProjectionItemArrayData;

        public function data(): iterable;
    }
    ```
	A trait called `ProjectionItemIterableTrait` is provided with those methods already implemented and with the data stored as an array, so just use it your projection item classes and you're good to go.

    Just bear in mind that the trait is only implementing the methods defined in `ProjectionItemIterable` and not those of `ProjectionItem` so it is still responsibility of your projection item class to implement them!

3. The **projection item** is projected through a repository which implements `ProjectionRepository` interface.

    This holds methods to get, add and delete the projections. The methods are used by passing a **projection item** object.

    ```
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
    This repository is called `CachePoolProjectionRepository`

### Using `CachePoolProjectionRepository`
1. Besides the Symfony's Tag Aware Cache Pool interface, this repository uses the JMS Serializer. The following snippet is the repository's constructor.
    ```
    public function __construct(TagAwareAdapterInterface $cachePool, SerializerInterface $serializer)
    {
        $this->cachePool  = $cachePool;
        $this->serializer = $serializer;
    }
    ```
    
    So there is the need to define the serialization config for the Projection items. For instance, for the previous `ExampleProjectionItem` example, here is an example of the JMS Serializer XML config for this class:
    ```
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

2. Usage with Symfony ^4.0

    Create a cache pool with Symfony config. Here's an example for the cache pool to use Memcached:
    ```
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
    ```
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
    ```
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
    ```
        App\Infrastructure\UseCase\Query\GetProfileCommonByUuid\DataProvider\ProjectionDataProvider:
            arguments:
                - '@Kununu/Projections/Repository/CachePoolProjectionRepository'
    
    ```
    
    And inside the respective class we should depend only on the `ProjectionRepository` interface.
    ```
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

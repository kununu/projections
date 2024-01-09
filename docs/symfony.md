# Integration with Symfony

If you want to use this library in a Symfony application, then it's recommended to use the `SymfonyCacheProjectionRepository` (with `symfony/cache` as the backend provider for caches).

In this example we will also show how to use the [JMS Serializer Bundle](https://github.com/schmittjoh/JMSSerializerBundle) as the serialization provider.

## Define the cache pool(s)

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

## Configure your serialization details

Your mileage may vary. Here we are using XML files to configure the serialization.

### Configure JMS serializer

Create a `jms_serializer.yaml` to configure your serialization and import it in one of your services yaml files.

`services.yaml`

```yaml
imports:
  - { resource: jms_serializer.yaml }
services:  
```

`jms_serializer.yaml`
```yaml
jms_serializer:
  metadata:
    directories:
      projections:
        namespace_prefix: "Kununu\Example"
        path: "%kernel.project_dir%/src/Repository/Resources/config/serializer"
```

where `%kernel.project_dir%/src/Repository/Resources/config/serializer` is the directory where the JMS Serializer configuration files for the projection items.

### Define XML serialization

Create a `ExampleProjectionItem.xml` inside the directory mentioned above. Please notice that the namespace prefix of the projection item class is also defined in here.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<serializer>
  <class name="Kununu\Example\ExampleProjectionItem">
    <property name="id" type="string"/>
    <property name="someValue" type="string"/>
  </class>
</serializer>
```

In this example you can see that the two properties of the projection item are on the config.

## Configure you projection repository

Next define your custom instance of `SymfonyCacheProjectionRepository` as a Symfony service:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  #  A tag-aware cache adapter
  example.cache.projections.tagged:
    class: Symfony\Component\Cache\Adapter\TagAwareAdapter
    decorates: 'example.cache.projections'
    
  # We want to use the JMSCacheSerializer which is a wrapper for JMS Serializer
  example.cache.jms_serializer:
    class: Kununu\Projections\Serializer\Provider\JMSCacheSerializer
    arguments:
      - '@jms_serializer'

  # My cached repository
  example.my.cached.repository:
    class: Kununu\Projections\Repository\SymfonyCacheProjectionRepository
    arguments:
      - '@example.cache.projections'
      - '@example.cache.jms_serializer'
```

Note that the `TagAwareAdapter` is added as a decorator for the cache pool service.

## Use the repository

Now you can inject the repository service in your classes that need to use it.

Example:

```yaml
Kununu\Example\MyProvider:
  arguments:
    - '@example.my.cached.repository'
```

And inside the respective class we should depend only on the `ProjectionRepositoryInterface` interface instance to project/get/delete data from the cache.

```php
namespace Kununu\Example;

use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class MyProvider
{
    public function __construct(private ProjectionRepositoryInterface $projectionRepository)
    {
    }

	public function someAction(): void
	{
		// We can use the projection repository to fetch/store data in the cache
		$item = new MyProjectionItemClass('the key for this item');
		$value = $this->projectionRepository->get($item);
		
		// Cache miss
		if (null === $value) {
		    // Is up to you to define what data to store on the item
            $item->setProperty1('value');
            $item->setProperty2(2040);
            
            $this->projectionRepository->add($item);
	    } else {
	        // Cache hit
	        // Use item fetched from the cache
	        var_export($item->getProperty1());
	    }
	}
	
	public function removeItems(): void
	{
	    // Remove all items in the cache that are tagged with 'a-tag'...
	    $this->projectionRepository->deleteByTags(new Tags(new Tag('a-tag')));
	}
}
```

Now we can start reading, setting and deleting from the cache pool :)

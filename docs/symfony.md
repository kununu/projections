# Integration with Symfony

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

Next define your custom instance of `CachePoolProjectionRepository` as a Symfony service:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  #  A tag-aware cache adapter
  example.cache.projections.tagged:
    class: Symfony\Component\Cache\Adapter\TagAwareAdapter
    decorates: 'example.cache.projections'

  # My cached repository
  app.my.cached.repository:
    class: Kununu\Projections\Repository\CachePoolProjectionRepository
    arguments:
      - '@example.cache.projections'
      - '@jms_serializer'

```

Note that the `TagAwareAdapter` is added as a decorator for the cache pool service.

Now you can inject the repository's service. Example:

```yaml
App\Infrastructure\UseCase\Query\GetProfileCommonByUuid\DataProvider\ProjectionDataProvider:
  arguments:
    - '@app.my.cached.repository'
```

#### Configure the serialization

For JMS Serializer you should need to configure the serialization.  Here is an example of the JMS Serializer XML config:

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<serializer>
  <class name="Kununu\Example\ExampleProjectionItem">
    <property name="id" type="string"/>
    <property name="someValue" type="string"/>
  </class>
</serializer>
```

This should be saved in a `ExampleProjectionItem.xml` file.

The data that you want projected needs exist on the serializer config in order to be actually projected.

In this example you can see that the two properties of the projection item are on the config.

This configuration needs to be loaded into the JMS Serializer and the repository needs to be instantiated in order to be used.


And inside the respective class we should depend only on the `ProjectionRepositoryInterface` interface instance to project/get/delete data from the cache.

```php
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\Tag\Tag;
use Kununu\Projections\Tag\Tags;

final class ProjectionDataProvider
{
    private $projectionRepository;

    public function __construct(ProjectionRepositoryInterface $projectionRepository)
    {
        $this->projectionRepository = $projectionRepository;
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

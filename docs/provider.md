# Provider

## AbstractCachedProvider

As projections are being used to project data to a cache provider we might end up having the need to create a "provider" for data that will check if the data is already on cache and if not try to fetch from the "real" source and then store it on cache (e.g. create a projection).

We can even use the **Decorator** pattern to achieve this.

Usually the flow is always the same.

- Get item from the cache
- Cache was hit? Return the data retrieved from cache
- Cache was miss? Call the original provider to fetch the data
    - Data was found?
        - Store it on the cache
        - Return the data
- Rinse and repeat...

So the `AbstractCachedProvider` will help you in reducing the boilerplate for those scenarios.

Your "provider" class should extend it and for each method where you need to use the flow described above you just need to call the `getAndCacheData` method:

```php
protected function getAndCacheData(
    ProjectionItemIterableInterface $item,
    callable $dataGetter,
    callable ...$preProjections
): ?iterable;
```

- The `$item` parameter is a [projection item](projection-item.md) that will be used to build the cache key
- The `$dataGetter` is your custom function that should return an `iterable` with you data or null if no data was found
    - Signature of the callable function:
        - `function(): ?iterable`
- The `$preProjections` are your custom functions that should manipulate the item/data before they are projected:
    - Signature of the callable functions:
        - `function(ProjectionItemIterableInterface $item, iterable $data): ?iterable`
    - The callable should return the `$data` that will be stored on the `ProjectionItemIterableInterface` instance via `storeData` method, so **do not call that method directly** in your callable!
    - The data will be propagated for each callable passed
    - If you want to *not store* data on the cache, then your callable should return `null` and the pre-processor chain will end

An example:

```php
use Kununu\Projections\ProjectionRepositoryInterface;
use Kununu\Projections\ProjectionItemIterableInterface;

interface MyProviderInterface
{
    public function getCustomerData(int $customerId): ?iterable;
}

final class MyProvider implements MyProviderInterface
{
    public function getCustomerData(int $customerId): ?iterable
    {
        // Let's grab the data from someplace (e.g. a database)...
        $result = ...

        return $result;
    }
}

/**
 * This class will decorate any MyProviderInterface instance (e.g. MyProvider) to use projections and read/write from cache
 */
final class MyCachedProvider extends AbstractCachedProvider implements MyProviderInterface
{
    public function __construct(
        private MyProviderInterface $myProvider,
        ProjectionRepositoryInterface $projectionRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($projectionRepository, $logger);
        $this->myProvider = $myProvider;
    }
    
    public function getCustomerData(int $customerId): ?iterable
    {
        return $this->getAndCacheData(
            new CustomerByIdProjectionItem($customerId),
            // This callable will get the data when there is a cache miss
            // (e.g. data was not found on the cache)
            fn(): ?iterable  => $this->myProvider->getCustomerData($customerId),
            // Additional callables to do pre-processing before projecting the
            // items to the cache. They are optional and only called in the event of
            // a cache miss (and after the data getter callable returns the data)
            function(ProjectionItemIterableInterface $item, iterable $data): ?iterable {
                // A case where I don't want to store the projection
                // because it does not have relevant information 
                if($data['customer_id'] > 200) {
                    return null;
                }
                
                // We could also add more info here...
				// E.g.: we fetch some data from database, but we need
				// to call some external API to get additional data.
				// This is a perfect place to do that
				$data['new_value'] = 500;
				
				// We could also set/change properties on the item
				$item->setUuid('f712e7af-41d0-4c3d-bbdb-0643197f9eed');

                return $data;
            }
        );        
    }
}

$projectionRepository = // Get/build your projection/repository
$logger = // Get/build your logger
$myProvider = // Get/build your "original" provider

$cachedProvider = new MyCachedProvider($myProvider, $projectionRepository, $logger);

$data = $cachedProvider->getCustomerData(152);
```

### CachedProviderTestCase

In order to help you unit testing your cached providers implementations the `CachedProviderTestCase` exists for that purpose.

Just make you test class extend it and override the `METHODS` constant and implement the `getProvider` method.

The `getProvider` is where you should create the "decorated" cached provider you want to test. E.g:

```php
protected function getProvider(mixed $originalProvider): AbstractCachedProvider
{
    return new MyCachedProvider($originalProvider, $this->getProjectionRepository(), $this->getLogger());
}
```

You don't need to mock the projection repository neither the logger. Just create an instance of your cached provider.

The `$originalProvider` will be an instance/mock of your original provider.

The `METHODS` constant should contain the methods of your provider class.

For our example above to test the `getCustomerData` method:

```php
protected const METHODS = [
    'getCustomerData',
];
```

Now, for each method defined in the `METHODS` constant you need to create a PHPUnit data provider method.

So in this case you would have to create a method called `getCustomerDataDataProvider`:

```php
public static function getCustomerDataDataProvider(): array
{
    return [
        'my_test_case_1' => [
            $originalProvider, // An instance/mock of your original provider 
            $method, // Should be 'getCustomerData' as this is a test case for that method
            $args, // Arguments to your method (int this case: [123 <- $customerId])
            $item, // Projection item to search in cache (e.g. new CustomerByIdProjectionItem(123))
            $projectedItem, // Projected item to be return by the projection repository (null to simulate a cache miss)
            $providerData, // Data the original provider will return
            $providerData, // Expected result
            // Optional, by default is null and only required when you are doing manipulations on your cached provider
            // before projecting the item to the cache.
            //
            // To test this cases you can change the item as you expect it before doing the projection
            // The $item received here is a clone of the $item defined above and if $providerData is iterable it
            // is already injected in the item via the storeData method
            function($itemToProject) {
                // Do something to the item before adding it to the cache
                // E.g. set a property on the item that usually is set on the pre-projection callables of the
                // getAndCacheData method of the cached provider              
                $itemToProject->setField('a value');

                return $itemToProject;
            }
        ]
    ]; 
}
```

If you want to mock the original provider you can do it with the `createExternalProvider`:

```php
protected static function createExternalProvider(
    string $providerClass,
    string $method,
    array $args,
    bool $expected,
    ?iterable $data
): MockObject
```

- `$providerClass` - The class/interface of your original provider
- `$method` - The method you are mocking
- `$args` - The expected arguments to the method
- `$expected` - If the call is expected to return data
- `$data` - The data to return

Example:

```php
$originalProvider = self::createExternalProvider(
    MyProviderInterface::class,
    'getCustomerData',
    [123],
    true,
    [
        'id' => 123,
        'name' => 'My Customer Name'
    ]
);
```

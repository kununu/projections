# Serialization




### Using `CachePoolProjectionRepository`

#### Serialization

Besides the Symfony's Tag Aware Cache Pool interface, this repository uses the JMS Serializer. The following snippet is the repository's constructor:

```php
public function __construct(TagAwareAdapterInterface $cachePool, SerializerInterface $serializer);
```

So there is the need to define the serialization config for the Projection items. For instance, for the previous `ExampleProjectionItem` example, here is an example of the JMS Serializer XML config for this class:

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

The data that you want projected needs exist on the serializer config in order to be actually projected. In this example you can see that the two properties of the projection item are on the config.

This configuration needs to be loaded into the JMS Serializer and the repository needs to be instantiated in order to be used.

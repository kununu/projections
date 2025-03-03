# Serialization

In order to project/read an [item](projection-item.md) to/from the cache we will need to serialize/deserialize it.

The repositories implementations provided by the library (or your custom repository) all extend `AbstractProjectionRepository`.

Besides the cache pool implementation they require an implementation of `CacheSerializerInterface`.

# CacheSerializerInterface

This interface defines the methods that a serializer/deserializer should implement to be used with an `AbstractProjectionRepository` concrete implementation.

```php
interface CacheSerializerInterface
{
    public function serialize(mixed $data): string;

    public function deserialize(string $data, string $class): mixed;
}
```

## Serialization Providers

### IgBinaryCacheSerializer

This implementation is a wrapper for the [igbinary serializer](https://www.php.net/manual/en/book.igbinary.php).

It requires the `igbinary` extension to be installed and enabled in your application.

```php
use Kununu\Projections\Repository\SymfonyCacheProjectionRepository;
use Kununu\Projections\Serializer\Provider\IgBinaryCacheSerializer;

$cachePool = // Get the symfony cache pool somehow
// Create a projection repository with the cache serialization wrapper
$repository = new SymfonyCacheProjectionRepository($cachePool, new IgBinaryCacheSerializer());

$repository->add($myItem);
```

### JMSCacheSerializer

This implementation is a wrapper for [JMS Serializer](https://jmsyst.com/libs/serializer).

It requires the `jms/serializer` library to be installed (directly or via `jms/serializer-bundle` if that bundle is used on Symfony applications).

```php
use Kununu\Projections\Repository\SymfonyCacheProjectionRepository;
use Kununu\Projections\Serializer\Provider\JMSCacheSerializer;

$cachePool = // Get the symfony cache pool somehow
$jmsSerializer = // Get the JMS Serializer somehow
// Create a projection repository with the cache serialization wrapper
$repository = new SymfonyCacheProjectionRepository($cachePool, new JMSCacheSerializer($jmsSerializer));

$repository->add($myItem);
```

### PhpCacheSerializer

This implementation is a wrapper for PHP's [internal serializer](https://www.php.net/manual/en/function.serialize.php) and [internal un-serializer](https://www.php.net/manual/en/function.unserialize).

```php
use Kununu\Projections\Repository\Psr6CacheProjectionRepository;
use Kununu\Projections\Serializer\Provider\PhpCacheSerializer;

$cachePool = // Get the cache pool somehow
$jmsSerializer = // Get the JMS Serializer somehow
// Create a projection repository with the cache serialization wrapper
$repository = new Psr6CacheProjectionRepository($cachePool, new PhpCacheSerializer());

$repository->add($myItem);
```

### SymfonyCacheSerializer

This implementation is a wrapper for [Symfony Serializer](https://symfony.com/doc/5.x/components/serializer.html).

It requires the `symfony/property-access` and `symfony/serializer` to be installed. 

```php
use Kununu\Projections\Repository\SymfonyCacheProjectionRepository;
use Kununu\Projections\Serializer\Provider\SymfonyCacheSerializer;

$cachePool = // Get the symfony cache pool somehow
$symfonySerializer = // Get the Symfony Serializer somehow
// Create a projection repository with the cache serialization wrapper
$repository = new SymfonyCacheProjectionRepository($cachePool, new SymfonyCacheSerializer($symfonySerializer));

$repository->add($myItem);
```

## Compressed Serialization Providers

The library also provides the `AbstractDeflateCacheDecoratorSerializer` which will decorate any cache serializer to compress/decompress the data before/after serialization/deserialization.

It requires the `zlib` extension to be installed and enabled in your application.

The following compressed providers are available with the library:

- `IgBinaryDeflatedCacheSerializer`
- `JMSDeflatedCacheSerializer`
- `PhpDeflatedCacheSerializer`
- `SymfonyDeflatedCacheSerializer`

**ADVICE**

If using `SymfonyCacheProjectionRepository` (which uses Symfony cache components) it is advisable to not use these serialization providers.

Instead, decorate the default cache marshaller (bonus: you can configure this per environment, meaning you can have uncompressed cache for your development environment, but compress it on production).

Small example on how to achieve this:

```yaml
services:
  # Decorates Symfony default marshaller to compress cache items
  Symfony\Component\Cache\Marshaller\DeflateMarshaller:
    decorates: cache.default_marshaller
    arguments: [ '@.inner' ]
```

---

[Back to Index](../README.md)

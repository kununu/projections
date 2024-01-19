# Kununu Projections

Projections are a temporary storage and are a way to access data faster than fetching it from a regular storage (e.g. getting data from a cache vs from the database).

Data needs to be projected first so that its projection can be accessed without the need to access the actual source of truth, which is usually a slower process.

Projections have a short lifetime, and are not updated automatically if data in source of truth changes. So they need to be frequently refreshed.

## Overview

This repository contains the interfaces to implement projections logic.

It also includes an implementation of the projection over the Symfony's Tag Aware Cache Pool component, which can use several cache providers, like Memcached, Redis or simply process memory, amongst others.

## Installation

### Require this library to your project

```bash
composer require kununu/projections
```

### If you wish to have projections implemented via Symfony's Tag Aware Cache Pool, you must also request the required packages for that implementation
 
```bash
composer require symfony/cache
```

Also you will need to include a serializer (e.g. JMSSerializer)

```bash
composer require jms/serializer
```

(Or, in this example, if you want to use this library on a Symfony App you may want to require the `jms/serializer-bundle` instead of `jms/serializer`)

```bash
composer require jms/serializer-bundle
```

## Concepts

- [Projection Item](docs/projection-item.md)
- [Repository](docs/repository.md)
- [Provider](docs/provider.md)
- [Serialization](docs/serialization.md)

## Integrations
- [Symfony Integrations](docs/symfony.md)

------------------------------

![Continuous Integration](https://github.com/kununu/projections/actions/workflows/continuous-integration.yml/badge.svg)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=kununu_projections&metric=alert_status)](https://sonarcloud.io/dashboard?id=kununu_projections)


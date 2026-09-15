# AGENTS.md

Steering notes for AI agents working in this repository. For installation and
usage see [README.md](README.md); for dev setup, testing, and the PR process see
[CONTRIBUTING.md](CONTRIBUTING.md).

## What this is

`kununu/projections` is a PHP library that handles projections of items to a
cache. A projection is short-lived, faster-to-read storage (e.g. cache) placed
in front of a slower source of truth. The library ships the interfaces for
projection logic plus an implementation over Symfony's Tag Aware Cache Pool.

Consumed as a Composer package (`kununu/projections`); it is not a deployable
service.

## Domain concepts

- **Projection item** — the unit that is projected, identified by a key and
  carrying tags (`ProjectionItemInterface`).
- **Repository** — projects, reads, and deletes items
  (`ProjectionRepositoryInterface`); `add`/`addDeferred`/`flush`, `get`,
  `delete`, `deleteByTags`.
- **Provider** — orchestrates fetch-or-cache around a repository
  (`AbstractCachedProvider`).
- **Serializer** — encodes items for cache storage (`CacheSerializerInterface`),
  with PHP, IgBinary, JMS, and Symfony variants plus deflate decorators.
- **Cache cleaner** — invalidates projections by tags (`CacheCleanerInterface`).
- **Tag / Tags** — value objects for tagging projections.

Deep docs live in `docs/` and are indexed from the README: cache-cleaner,
projection-item, provider, repository, serialization, symfony.

## Code layout

- `src/` — library code, PSR-4 `Kununu\Projections\`.
  - `CacheCleaner/`, `Provider/`, `Repository/`, `Serializer/`, `Tag/`,
    `Exception/`.
  - `TestCase/` — reusable abstract test cases and traits shipped for consumers
    to test their own providers, cache cleaners, and repositories.
- `tests/` — PHPUnit tests, PSR-4 `Kununu\Projections\Tests\`. Reusable test
  fixtures live in `tests/Stubs/`.
- `docs/` — concept documentation.

## Quality gates

PHP version is pinned in `composer.json` (`require.php`). Run via Composer
scripts (see `scripts` in `composer.json`):

- `composer test` — PHPUnit.
- `composer phpstan` — static analysis.
- `composer cs` — PHP CS Fixer (kununu standards).
- `composer sniffer` — PHP_CodeSniffer (`sniffer-fix` to autofix).
- `composer rector` — Rector dry-run (`rector-fix` to apply).

CI (`.github/workflows/continuous-integration.yml`) additionally runs
composer-dependency-analyser, composer-require-checker, composer-normalize, and
a SonarCloud scan.

## Hard constraints

- Every code change must ship with tests; PRs without tests are not accepted.
- Follow kununu coding standards (PSR-2 extension via `kununu/code-tools`);
  keep `declare(strict_types=1);` in every PHP file.
- Public API changes are subject to SemVer — avoid breaking changes where
  possible and document them.
- Keep runtime dependencies optional: extra packages (Symfony cache, JMS, ext-*)
  are declared under `require-dev`/`suggest`, not `require`.

# Contributing

Contributions are more than **welcome**. We accept contributions via Pull
Requests on [GitHub](https://github.com/kununu/projections).

## Development setup

Install the dependencies with Composer:

```bash
composer install
```

The [kununu Coding Standards](https://github.com/kununu/kununu-scripts) are an
extension of [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
The `kununu/code-tools` package is installed as a dev dependency and exposes the
commands used to meet those standards.

## Quality gates

Run the checks locally before opening a pull request (see the `scripts` section
of `composer.json` for the full list):

```bash
composer test       # PHPUnit
composer phpstan    # static analysis
composer cs         # PHP CS Fixer (kununu standards)
composer sniffer    # PHP_CodeSniffer (composer sniffer-fix to autofix)
composer rector     # Rector dry-run (composer rector-fix to apply)
```

Use `composer test-coverage` to run the suite with a coverage report.

The same checks run in CI
(`.github/workflows/continuous-integration.yml`), which also runs
composer-dependency-analyser, composer-require-checker, composer-normalize, and
a SonarCloud scan.

## Pull requests

- **Add tests** — to keep a high quality code base, a patch cannot be accepted
  if it does not have tests.
- **Document any change in behaviour** — keep `CHANGELOG.md`, `README.md`, and
  any other relevant documentation up to date.
- **Consider the release cycle** — this library uses semantic versioning
  ([SemVer v2.0.0](https://semver.org/)). Changes to the public API must be made
  with great consideration and avoided where possible.
- **Create feature branches** — `main` is the stable branch; create a new branch
  for each feature.
- **One pull request per feature** — send multiple pull requests if you want to
  do more than one thing.
- **Be respectful** — see our [Code of Conduct](CODE_OF_CONDUCT.md).

**Happy coding**!

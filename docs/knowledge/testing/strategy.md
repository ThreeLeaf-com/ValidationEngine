---
type: Testing Strategy
title: Testing Strategy
description: PHPUnit with Orchestra Testbench, two suites, an in-memory SQLite database with foreign keys enabled, and clover plus HTML coverage into target/.
resource: phpunit.xml
tags: [testing, phpunit, testbench, sqlite, coverage]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

## Frameworks

- PHPUnit `^11.0`
- Orchestra Testbench `^10.0` — boots a Laravel application around the package

Testbench is what makes a package testable without a host application. Note that
the project `agents/.agent-config.json` metadata names Pest and Laravel Dusk;
neither is in `composer.json` and no test uses them. PHPUnit is the framework in
use.

## Suites

`phpunit.xml` declares two suites:

| Suite     | Directory       | Character                                        |
| --------- | --------------- | ------------------------------------------------ |
| `Unit`    | `tests/Unit`    | Rule classes and the cast, no database           |
| `Feature` | `tests/Feature` | Models, services, and controllers, with database |

Feature tests extend `Tests\Feature\TestCase`, which extends
`Orchestra\Testbench\TestCase`.

## Test environment

`TestCase::getEnvironmentSetUp()` sets the default connection to `testing` and
defines it as SQLite `:memory:`.

`TestCase::setUp()` then does two things that matter:

1. `setUpRoutes()` registers `routes/api.php` under the `api` middleware group
   with an `api` prefix. The package does not register routes, so without this
   step no controller test could reach an endpoint.
2. When the driver is SQLite, it runs `PRAGMA foreign_keys=ON`. SQLite ignores
   foreign keys by default, so the cascade behavior of
   [`v_validator_rules`](/data/models/validator-rules-table.md) would go untested
   without this line.

`getPackageProviders()` returns `ValidationServiceProvider`, which loads the
migrations.

## Factories

`database/factories` holds `RuleFactory`, `ValidatorFactory`, and
`ValidatorRuleFactory`. They are mapped through the
`Database\Factories\ThreeLeaf\ValidationEngine\Models\` PSR-4 namespace in
`composer.json`, which is what lets `HasFactory` resolve them for models in the
package namespace.

## Notable tests

`tests/Feature/Models/ReadmeTest.php` executes the usage example from
`README.md`. Changing that example without changing this test breaks the build —
which is the point.

## Coverage

`phpunit.xml` limits coverage source to `./src` and writes two reports:

- `target/coverage/clover.xml`
- `target/coverage/` (HTML)

`target/` is git-ignored. Refresh the README badge with
`php util/generate-coverage-badge.php` — see
[the DevOps guide](../../devops/README.md).

# Examples

```bash
./vendor/bin/phpunit

./vendor/bin/phpunit --testsuite Unit

./vendor/bin/phpunit --coverage-html target/coverage
```

Continuous integration runs the whole suite on PHP 8.2 for pushes and pull
requests against `main`, via
[`.github/workflows/tests.yaml`](../../../.github/workflows/tests.yaml).

# Citations

- Verified 2026-09-04 against git HEAD — `phpunit.xml` suites, source include, and both coverage reports
- Verified 2026-09-04 against git HEAD — `tests/Feature/TestCase.php` registers routes and enables SQLite foreign keys
- Verified 2026-09-04 against git HEAD — `composer.json` requires phpunit ^11.0 and testbench ^10.0, and lists no Pest or Dusk dependency
- Verified 2026-09-04 against git HEAD — `.github/workflows/tests.yaml` pins PHP 8.2
- `tests/Feature/Models/ReadmeTest.php`

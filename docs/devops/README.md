# DevOps Guide

Internal operations for the ValidationEngine package. Audience: maintainers.

This package is a Composer library published to Packagist. It has no servers, no
environments, and no deployment in the usual sense. "Release" means tagging a
version and letting Packagist pick it up.

For internals, see the [Technical Manual](../knowledge/index.md). For consumer
instructions, see the [User Guide](../user-guide/README.md).

## Repository facts

| Item           | Value                                                      |
| -------------- | ---------------------------------------------------------- |
| Package        | `threeleaf/validation-engine`                              |
| Packagist      | https://packagist.org/packages/threeleaf/validation-engine |
| Default branch | `main`                                                     |
| License        | GPL-3.0+                                                   |
| CI             | None (the GitHub Actions workflow was removed)             |

There is no staging branch. Work branches are cut from `main` and merge back to
`main`.

## Continuous integration

The repository has no CI workflow. The GitHub Actions test workflow was
removed, and `agents/.agent-config.json` sets `versionControl.ciEnabled` to
`false`. Run the test suite locally before a merge (see Local setup).

`composer.json` sets `config.platform.php` to `8.2`, so Composer resolves
dependencies for PHP 8.2 whatever PHP version runs it.

## Local setup

```bash
composer update && composer install
```

`composer install` triggers the `post-install-cmd` hook, which runs
`php util/generate-open-api.php`. When the generator fails, it exits with
code 1, and the install fails too. swagger-php is a development dependency, so
`composer install --no-dev` skips the generator with a message.

A `zircote/swagger-php` upgrade can fail the install. The generator uses
swagger-php classes directly, so a major version that removes a class it
imports stops the hook with a fatal error. swagger-php 7 removed
`OpenApi\SourceFinder`; the generator uses `OpenApi\Utils\SourceFinder`,
which exists in swagger-php 6.3.1 and later. `composer.json` therefore
requires `zircote/swagger-php` `^6.3.1 || ^7.0` and `darkaonline/l5-swagger`
`^11.0` in `require-dev`.
`tests/Unit/Util/GenerateOpenApiScriptTest.php` runs the generator, so the
test suite also shows this failure.

Run the tests:

```bash
./vendor/bin/phpunit
```

## Release procedure

1. Confirm the test suite passes on `main`.
2. Update the `version` field in `composer.json`.
3. Run `composer update && composer install` so `composer.lock` matches.
4. Commit and push to `main`.
5. Create and push a git tag for the version:

    ```bash
    git tag v2.0.0
    git push origin v2.0.0
    ```

6. Packagist updates from the GitHub webhook. If it does not, sign in to
   Packagist and use the **Update** button on the package page.

Packagist reads the tag, so the tag is the release. A `composer.json` version
bump with no tag publishes nothing.

## Regenerate the OpenAPI specification

```bash
php util/generate-open-api.php
```

`util/generate-open-api.php` is the only generator in `util/`. Earlier revisions
of `README.md` named a `util/generate-swagger.php` that never existed; that
reference has been corrected.

This generator currently emits `Required @OA\Info() not found` and
`Required @OA\PathItem() not found`, and reports zero paths found. The warnings
are pre-existing on `main` and do not fail the build. swagger-php reads
docblock `@OA\` annotations only when `doctrine/annotations` is installed, and
it is not, so the generator reads no annotations from `src/` and writes only
`{"openapi": "3.0.0"}`. Fixing that is separate work.

## Refresh the coverage badge

Run the suite with coverage, then regenerate the badge that `README.md` embeds:

```bash
./vendor/bin/phpunit --coverage-clover target/coverage/clover.xml
php util/generate-coverage-badge.php
```

The badge is written to `public/images/coverage-badge.svg`, which **is** tracked.
`target/` is git-ignored.

## Documentation

The documentation bundle is validated with:

```bash
python3 scripts/okf_validate.py docs/knowledge
```

Fix every error and every warning before merging a documentation change. See the
`okf-bundle` skill for the concept format.

## Agent configuration

`agents/.agent-config.json` is **git-ignored** in this repository, matching the
convention in `developer-toolkit`. It is a per-checkout file. A schema change to
it must be applied in each working copy; it does not travel in a pull request.
The same applies to `.cursorrules` and `work-items/`.

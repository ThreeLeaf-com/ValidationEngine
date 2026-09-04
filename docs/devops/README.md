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
| CI             | `.github/workflows/tests.yaml`                             |

There is no staging branch. Work branches are cut from `main` and merge back to
`main`.

## Continuous integration

[`.github/workflows/tests.yaml`](../../.github/workflows/tests.yaml) runs on
pushes and pull requests targeting `main`. It sets up PHP 8.2 with the
`mbstring`, `pdo_sqlite`, `zip`, and `curl` extensions, runs
`composer install --prefer-dist --no-progress`, copies `.env.example` to `.env`,
and runs `vendor/bin/phpunit --configuration phpunit.xml`.

Note that CI pins PHP 8.2 while `composer.json` allows `>=8.2`. A change that
needs a later version must update the workflow as well as the constraint.

## Local setup

```bash
composer update && composer install
```

`composer install` triggers the `post-install-cmd` hook, which runs
`php util/generate-open-api.php`. A syntax error in an `@OA\` annotation
therefore fails the install, not just the documentation build.

Run the tests:

```bash
./vendor/bin/phpunit
```

## Release procedure

1. Confirm CI is green on `main`.
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

The `README.md` "Miscellaneous" section names `util/generate-swagger.php`. That
file does not exist; `util/generate-open-api.php` is the correct path.

This generator currently emits `Required @OA\Info() not found` and
`Required @OA\PathItem() not found`, and reports zero paths found. The warnings
are pre-existing on `main` and do not fail the build, because the annotations
carry no top-level `@OA\Info` block. Fixing that is separate work.

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

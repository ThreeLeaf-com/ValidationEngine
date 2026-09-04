---
type: Style Guide
title: Style Conventions
description: Comment style, PHPDoc requirements, migration column comments, and embedded OpenAPI annotation rules as practiced in this package.
resource: .cursorrules
tags: [style, phpdoc, openapi, conventions]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

The authoritative rule set is [`.cursorrules`](../../../.cursorrules) at the
repository root. This concept records the conventions that the package's own
source actually follows.

## Comments

Inline explanatory comments use the block form, not `//`:

```php
/* Enable foreign key constraints for SQLite */
DB::statement('PRAGMA foreign_keys=ON;');
```

Comments state intent. They do not restate the statement below them.

## PHPDoc

- Every public method carries a docblock with `@param` and `@return`.
- Every model documents its columns with `@property` lines that give the type
  and a description.
- Relations are documented as `@property-read` with the relation's generic type,
  for example `@property-read BelongsTo<Validator> $validator`.
- Models add `@mixin Builder` plus `@method static` lines for `create`, `find`,
  and `query`, so static calls resolve in an IDE.
- `{@link ClassName}` is used to cross-reference types in prose.
- Thrown exceptions are declared with `@throws`.

## Migrations

Every table and every column carries a comment. The column comment mirrors the
model's `@property` description for the same field.

```php
$table->comment('Stores individual validation rules with their configurations.');
$table->uuid('rule_id')->primary()->comment('The unique identifier for the Rule.');
```

Timestamps use the standard pair:

```php
$table->timestamp(Model::CREATED_AT)->useCurrent()->comment('...');
$table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('...');
```

`up()` and `down()` each get a single-line docblock.

## OpenAPI annotations

The API specification is not a separate file. It lives in `@OA\` annotations
inside model and controller docblocks, and is extracted by
`util/generate-open-api.php`.

- Models declare `@OA\Schema` with a `schema` name matching the class name.
- Every property declares `type`, `description`, and an `example`.
- UUID properties add `format="uuid"`.
- A property that refers to another schema uses `ref="#/components/schemas/..."`
  rather than repeating the definition.
- `required={...}` lists the non-nullable fields.

An annotation syntax error breaks the Composer `post-install-cmd` hook, because
that hook runs the generator. Keep annotations valid.

## Naming

- Tables are prefixed `v_` through `ValidatorEngineConstants::TABLE_PREFIX`.
  Do not write the prefix as a literal.
- Models expose `TABLE_NAME` and `PRIMARY_KEY` constants, and reference those
  constants rather than repeating strings in requests and relations.
- Rule classes end in `Rule` and live in `src/Rules`.

## Markdown

Documentation in `docs/` is formatted with Prettier:

```bash
npx prettier --write "docs/**/*.md"
```

# Examples

A model property block, as written in `src/Models/ValidatorRule.php`:

```php
/**
 * @property string       $validator_id  The unique ID of the {@link Validator}
 * @property int          $order_number  The order in which the rule should be applied
 * @property ActiveStatus $active_status Whether the rule is currently active
 */
```

# Citations

- Verified 2026-09-04 against git HEAD — block-comment style in `tests/Feature/TestCase.php` and `src/Services/RuleService.php`
- Verified 2026-09-04 against git HEAD — `@property`, `@property-read`, `@mixin`, and `@method static` usage in `src/Models/`
- Verified 2026-09-04 against git HEAD — table and column comments in `database/migrations/2024_10_10_000000_create_validation_engine_tables.php`
- Verified 2026-09-04 against git HEAD — `@OA\Schema` blocks in `src/Models/Rule.php` and `src/Models/ValidatorRule.php`
- Verified 2026-09-04 against git HEAD — `composer.json` `post-install-cmd` runs `util/generate-open-api.php`
- `.cursorrules`

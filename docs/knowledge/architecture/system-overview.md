---
type: Architecture
title: System Overview
description: Package structure of the ValidationEngine Laravel library, the rule/validator/join model, and the path a validation request takes through the service layer.
resource: src/Providers/ValidationServiceProvider.php
tags: [architecture, laravel, package, validation]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

ValidationEngine is a Laravel **library package**, not an application. The host
application installs it with Composer and gets three database tables, an HTTP
API, and a set of validation rule classes.

## Package registration

`ValidationServiceProvider` is the single entry point. Composer's `extra.laravel.providers`
key auto-discovers it. The provider does one thing in `boot()`: it loads the
migrations in `database/migrations`. It registers no routes, no middleware, and
no container bindings.

Because the provider does not load `routes/api.php`, the host application must
register those routes itself if it wants the HTTP API. The test suite does this
in its own `setUp()` — see [Testing Strategy](/testing/strategy.md).

## The three-part model

| Concept       | Table               | Role                                              |
| ------------- | ------------------- | ------------------------------------------------- |
| Rule          | `v_rules`           | One check: an attribute, a rule class, parameters |
| Validator     | `v_validators`      | A named group of rules                            |
| ValidatorRule | `v_validator_rules` | Join row that adds order and active status        |

A `Validator` reaches its rules through a `belongsToMany` relation across the
join table. Full column detail is in [the data model](/data/models/index.md).

The design goal is that validation logic lives in **data**, not in code. An
operator adds or reorders a rule by writing a row, and no deployment is needed.

## Layers

```
routes/api.php
  -> Http/Controllers/Api/*Controller
       -> Http/Requests/*Request      (input validation)
       -> Services/ValidatorService   (runValidator, runValidatorById)
            -> Services/RuleService   (validateRules, compileRule)
                 -> Models/Rule::instantiateRule()
                      -> Rules/<Type>Rule::isValidFor()
```

`ValidatorService` holds a readonly `RuleService` injected through the
constructor. `RuleService` has no dependencies.

## The validation path

`ValidatorService::runValidatorById()` accepts either a `validator_id` or a
`name`. It matches on both columns and requires `active_status` to be `ACTIVE`.
If no validator matches, it returns `false` — a missing validator and a failed
validation give the same answer to the caller.

`RuleService::validateRules()` loops the rules in order. For each rule it calls
`Rule::instantiateRule()`, then `isValidFor($data[$rule->attribute] ?? null)`.
A missing attribute passes `null` to the rule rather than skipping the rule.
The loop stops at the first failure.

Note that `validateRules()` reads the rules through `$validator->rules()->get()`,
which is the `belongsToMany` relation. That relation selects from `v_rules` and
does **not** order by the join table's `order_number`, nor does it filter on the
join row's `active_status`. Both columns exist and are maintained by the API, but
the execution path does not yet read them.

# Examples

Run a validator by name:

```php
use ThreeLeaf\ValidationEngine\Services\ValidatorService;

$passes = app(ValidatorService::class)
    ->runValidatorById('StateAndTimeValidator', ['active_status' => 'Active']);
```

# Citations

- Verified 2026-09-04 against git HEAD — `src/Providers/ValidationServiceProvider.php` loads migrations only
- Verified 2026-09-04 against git HEAD — `src/Services/ValidatorService.php`, `src/Services/RuleService.php` call chain
- Verified 2026-09-04 against git HEAD — `src/Models/Validator.php` `rules()` relation carries no `orderBy` or `wherePivot`
- `composer.json` (`extra.laravel.providers`)
- `database/migrations/2024_10_10_000000_create_validation_engine_tables.php`

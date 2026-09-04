---
type: Style Guide
title: Project Glossary
description: Shared terms used across the ValidationEngine bundle, with the precise meaning each one carries in this codebase.
resource: src/Constants/ValidatorEngineConstants.php
tags: [glossary, shared-language]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

Several of these words also have a Laravel meaning. Where the two differ, the
difference is stated, because that collision is the main source of confusion in
this codebase.

**Rule**
A row in [`v_rules`](/data/models/rules-table.md). It names one attribute, one
rule class, and that class's parameters. Not to be confused with a Laravel
validation rule string such as `required|string`.

**Rule class / rule type**
A PHP class extending `ValidationEngineRule`, named by `v_rules.rule_type`. The
eight that ship with the package are listed in [Rule Types](/features/rule-types.md).

**Validator**
A row in [`v_validators`](/data/models/validators-table.md): a uniquely named,
optionally contextualized group of rules. **Not** Laravel's `Validator` facade.
Both names appear in `README.md`, where the Laravel one is imported as
`LaravelValidator` to keep them apart.

**ValidatorRule**
A row in [`v_validator_rules`](/data/models/validator-rules-table.md), joining
one validator to one rule and adding `order_number` and `active_status`.

**Attribute**
Two unrelated meanings. In `v_rules.attribute` it is the key in the data payload
that a rule checks. In `ValidationEngineRule` it is an entry in the class's
internal `$attributes` array, holding a constructor argument.

**Parameters**
The JSON object on a rule row. Its keys must match the rule class constructor's
parameter names — see
[Rule Persistence and Instantiation](/features/rule-persistence-and-instantiation.md).

**Context**
A nullable free-form label on a validator. The package stores it and does not
interpret it.

**Active status**
The `ActiveStatus` enum, `Active` or `Inactive`. It appears on both validators
and join rows. Only the validator's value is consulted at validation time.

**Order number**
Two different columns. On `v_validators` it orders validators. On
`v_validator_rules` it orders rules inside one validator. Neither is currently
read by the validation path.

**Table prefix**
`v_`, defined once as `ValidatorEngineConstants::TABLE_PREFIX`.

**Host application**
The Laravel application that installs this package. It owns routing,
middleware, and authorization — see
[Route Protection](/security/route-protection.md).

# Citations

- Verified 2026-09-04 against git HEAD — `src/Enums/ActiveStatus.php` cases
- Verified 2026-09-04 against git HEAD — `src/Constants/ValidatorEngineConstants.php` defines `TABLE_PREFIX` as `v_`
- Verified 2026-09-04 against git HEAD — `README.md` aliases Laravel's facade as `LaravelValidator`
- Verified 2026-09-04 against git HEAD — `$attributes` array in `src/Rules/ValidationEngineRule.php`

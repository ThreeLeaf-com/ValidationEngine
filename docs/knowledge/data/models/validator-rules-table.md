---
type: Database Table
title: v_validator_rules
description: Composite-key join table that links a validator to a rule and adds an order number and an active status.
resource: src/Models/ValidatorRule.php
tags: [database, eloquent, join-table, composite-key]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

Table `v_validator_rules`. Model `ThreeLeaf\ValidationEngine\Models\ValidatorRule`.

| Column          | Type      | Notes                                                    |
| --------------- | --------- | -------------------------------------------------------- |
| `validator_id`  | UUID      | Composite primary key part 1. FK to `v_validators`.      |
| `rule_id`       | UUID      | Composite primary key part 2. FK to `v_rules`.           |
| `order_number`  | integer   | Default `1`. Intended order of rule application.         |
| `active_status` | enum      | `Active` or `Inactive`. Default `Active`.                |
| `created_at`    | timestamp | Database default `useCurrent()`.                         |
| `updated_at`    | timestamp | Database default `useCurrent()`, `useCurrentOnUpdate()`. |

The primary key is `['validator_id', 'rule_id']`. Both foreign keys are declared
`onDelete('cascade')`.

Eloquent assumes a single scalar key, so this model uses the
[`HasCompositeKey`](/features/composite-key-models.md) trait to make save and
update target both columns.

## Relations

- `validator(): BelongsTo` — to `Validator` on `validator_id`
- `rule(): BelongsTo` — to `Rule` on `rule_id`

Both relations pass the same column name as local and foreign key, because the
join columns and the parent primary keys share their names.

## Fillable

`validator_id`, `rule_id`, `order_number`, `active_status`.

## Uniqueness

`ValidatorRuleRequest` enforces at the HTTP layer that `order_number` is unique
**within** one `validator_id`, and that a given `rule_id` appears at most once
per validator. The database does not enforce either rule; only the composite
primary key is enforced there. See
[Validator Rules Endpoints](/api/validator-rules-endpoints.md).

## Not yet read at validation time

`order_number` and `active_status` on this table are written and validated by
the API, but the `Validator::rules()` relation ignores both. See
[System Overview](/architecture/system-overview.md).

# Examples

```php
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

ValidatorRule::create([
    'validator_id'  => $validator->validator_id,
    'rule_id'       => $rule->rule_id,
    'order_number'  => 1,
    'active_status' => 'Active',
]);
```

# Citations

- Verified 2026-09-04 against git HEAD — `src/Models/ValidatorRule.php` `$primaryKeys`, relations, fillable
- Verified 2026-09-04 against git HEAD — migration declares composite primary key and two cascading foreign keys
- Verified 2026-09-04 against git HEAD — `src/Http/Requests/ValidatorRuleRequest.php` uniqueness rules
- `src/Traits/HasCompositeKey.php`

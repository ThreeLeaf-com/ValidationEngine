---
type: Feature
title: Composite-Key Models
description: The HasCompositeKey trait, which overrides setKeysForSaveQuery so Eloquent can update a row keyed by more than one column.
resource: src/Traits/HasCompositeKey.php
tags: [eloquent, composite-key, traits]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

Eloquent assumes one scalar primary key. It builds every save and update query
as `where <primaryKey> = <value>`. A table keyed by two columns therefore updates
the wrong rows, or none.

`HasCompositeKey` fixes the update path only.

## Contract

A model that uses the trait must declare:

```php
protected array $primaryKeys = ['validator_id', 'rule_id'];
```

Note the plural `$primaryKeys`. This is a **new** property, not Eloquent's own
`$primaryKey`. The trait's docblock says the array should be defined "in the
`$primaryKey` property", which does not match the code; the code reads
`$primaryKeys`.

## Methods

`getCompositeKeyNames(): array` returns `$this->primaryKeys`.

`setKeysForSaveQuery($query): Builder` overrides the Eloquent method of the same
name. For each key it adds:

```php
$query->where($key, '=', $this->getOriginal($key) ?? $this->getAttribute($key));
```

`getOriginal()` first, so a model whose key was changed in memory still updates
the row it was loaded from. `getAttribute()` is the fallback for a key with no
original value.

## What the trait does not do

- It does not set `$incrementing = false` or `$keyType`. Those are not needed
  here, because the join table is only ever written with both keys supplied.
- It does not make `find()` work. Callers look rows up with explicit `where`
  clauses — this is why
  [Validator Rules Endpoints](/api/validator-rules-endpoints.md) use no
  route-model binding.
- It does not add uniqueness beyond the composite primary key itself. The
  per-validator uniqueness of `order_number` is enforced in the form request.

Only [`ValidatorRule`](/data/models/validator-rules-table.md) uses this trait.

# Examples

```php
$validatorRule = ValidatorRule::where('validator_id', $validatorId)
    ->where('rule_id', $ruleId)
    ->firstOrFail();

$validatorRule->order_number = 3;
$validatorRule->save();
```

The `save()` above issues `where validator_id = ? and rule_id = ?`.

# Citations

- Verified 2026-09-04 against git HEAD — `src/Traits/HasCompositeKey.php` reads `$this->primaryKeys` while its docblock names `$primaryKey`
- Verified 2026-09-04 against git HEAD — `setKeysForSaveQuery` prefers `getOriginal()` over `getAttribute()`
- Verified 2026-09-04 against git HEAD — `src/Models/ValidatorRule.php` is the only model using the trait
- `tests/Feature/Models/ValidatorRuleTest.php`

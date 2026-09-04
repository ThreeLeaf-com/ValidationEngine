---
type: Security Control
title: Rule Class Instantiation From Stored Data
description: The rule_type column holds a class name that the engine instantiates by reflection, and the subclass checks that keep it from becoming arbitrary object construction.
resource: src/Models/Rule.php
tags: [security, reflection, deserialization, rules]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

`v_rules.rule_type` is a string that names a PHP class. At validation time the
engine instantiates that class with parameters that also came from the database.
This is the package's most security-relevant path, so the bounds on it matter.

## The two checks

Both checks require the class to be a subclass of `ValidationEngineRule`.

1. **On write** — `ClassCast::set()` rejects a value that is not an existing
   class, and then rejects a class that does not extend, implement, or use
   `ValidationEngineRule`.
2. **On read** — `Rule::instantiateRule()` repeats the check with
   `class_exists()` and `is_subclass_of()` before calling `make()`.

The second check is the one that holds. A row written by a seeder, a raw query,
or a migration bypasses the cast entirely, so read-side validation is what
actually bounds the class set.

## Why the bound is meaningful

`ValidationEngineRule` is an abstract class in this package. Only the eight
classes in `src/Rules` extend it, unless the host application adds its own. The
reachable constructor set is therefore small and known, and every member of it
takes scalar, array, or enum arguments.

`make()` uses `ReflectionClass::newInstanceArgs()`. Reflection here selects
**which** arguments to pass by parameter name; it does not widen the set of
classes that can be constructed. That set is fixed by the `is_subclass_of` gate
above it.

## Residual risk

- A host application that defines its own `ValidationEngineRule` subclass with a
  side-effecting constructor widens this surface. Keep rule constructors free of
  I/O.
- `RuleService::compileRule()` builds through the Laravel container with
  `makeWith()` and performs **no** subclass check of its own. It catches
  `Throwable`, logs, and returns `null`. It is not on the `validateRules()` path,
  but a caller that uses it directly loses the read-side gate.
- Write access to `v_rules` is equivalent to selecting which of those classes
  runs. Control it with [route protection](route-protection.md).

## What is not a risk here

`parameters` is stored as JSON and cast with Laravel's `'json'` cast. It is
`json_decode`d, not `unserialize`d, so it cannot carry a PHP object graph.

# Examples

A rejected write:

```php
Rule::create([
    'attribute' => 'x',
    'rule_type' => \Illuminate\Support\Facades\DB::class,
    'parameters' => [],
]);
// InvalidArgumentException: ... does not extend, implement, or use ValidationEngineRule
```

# Citations

- Verified 2026-09-04 against git HEAD — `src/Casts/ClassCast.php` `set()` performs the class and subclass checks
- Verified 2026-09-04 against git HEAD — `src/Models/Rule.php` `instantiateRule()` repeats them before `make()`
- Verified 2026-09-04 against git HEAD — `src/Services/RuleService.php` `compileRule()` has no subclass check and swallows `Throwable`
- Verified 2026-09-04 against git HEAD — `parameters` uses the Eloquent `json` cast, not serialization
- `src/Rules/ValidationEngineRule.php` (`make`)

---
type: Feature
title: Rule Persistence and Instantiation
description: How a v_rules row becomes a live rule object through ClassCast on write and instantiateRule plus the reflection-based make factory on read.
resource: src/Casts/ClassCast.php
tags: [rules, eloquent, casts, reflection]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

A rule is stored as data and rebuilt as an object. Three pieces do the work.

## 1. `ClassCast` — on write

`ClassCast` implements `CastsAttributes` and is applied to `Rule::$rule_type` as
`ClassCast::class . ':' . ValidationEngineRule::class`. The cast argument becomes
the constructor's `$classType`.

- The constructor rejects a `$classType` that is not an existing class,
  interface, or trait.
- `set()` rejects a value that is not an existing class, then rejects a value
  that does not extend, implement, or use `$classType`.
- `get()` returns the stored string unchanged. The cast is a write-side guard,
  not a hydrator.

Both rejections throw `InvalidArgumentException`.

## 2. `Rule::instantiateRule()` — on read

```php
public function instantiateRule(): ValidationEngineRule
```

It re-checks `class_exists($this->rule_type)` and
`is_subclass_of($this->rule_type, ValidationEngineRule::class)`, throws
`InvalidArgumentException` when either fails, then calls
`$this->rule_type::make($this->parameters)`.

The check is deliberately repeated here, because a row can reach the database
without passing through the cast — a raw insert, a seeder, or a direct query.

## 3. `ValidationEngineRule::make()` — the factory

`make(array $attributes): static` builds the instance by reflection:

1. Read the constructor's parameter list.
2. For each parameter, take `$attributes[$name]` when that **key** is present.
3. Otherwise use the parameter's default value.
4. Otherwise throw `InvalidArgumentException("Missing required attribute: $name")`.

A class with no constructor is built with `new $class()`. A `ReflectionException`
is re-thrown as `InvalidArgumentException`.

The consequence worth remembering: **the JSON keys in `parameters` must match
the constructor parameter names exactly.** Renaming a constructor parameter is a
breaking change for every stored row that names it.

`make()` passes values through untouched. It performs no type coercion, so a
parameter typed `DayOfWeek` needs an actual enum instance, not the string
`'Monday'`. Rule classes that accept loose input, such as `EnumRule`, do that
conversion in their own constructors.

## A second path: `RuleService::compileRule()`

`compileRule()` builds the rule through the Laravel container with
`Container::getInstance()->makeWith($rule->rule_type, $rule->parameters)`, and
returns `null` after logging when anything throws. It is a separate,
exception-swallowing path and is not what `validateRules()` uses;
`validateRules()` calls `instantiateRule()`.

# Examples

```php
$rule = Rule::create([
    'attribute'  => 'day',
    'rule_type'  => DaysOfWeekRule::class,
    'parameters' => ['daysOfWeek' => [DayOfWeek::MONDAY], 'timezone' => 'UTC'],
]);

$rule->instantiateRule()->isValidFor('2026-09-07');
```

# Citations

- Verified 2026-09-04 against git HEAD — `src/Casts/ClassCast.php` `get()` returns the raw value; `set()` performs both checks
- Verified 2026-09-04 against git HEAD — `src/Models/Rule.php` `instantiateRule()` repeats the class checks before calling `make()`
- Verified 2026-09-04 against git HEAD — `ValidationEngineRule::make()` matches by parameter name and falls back to defaults
- Verified 2026-09-04 against git HEAD — `src/Services/RuleService.php` `compileRule()` uses the container and returns null on failure
- `tests/Unit/Casts/ClassCastTest.php`

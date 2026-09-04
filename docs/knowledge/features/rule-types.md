---
type: Feature
title: Rule Types
description: The eight concrete ValidationEngineRule subclasses, their constructor parameter names, and their default values.
resource: src/Rules/ValidationEngineRule.php
tags: [rules, validation, enum, time]
timestamp: 2026-09-04T00:00:00Z
---

# Schema

Every rule class extends the abstract `ValidationEngineRule`, which implements
Laravel's `ValidationRule` contract plus `Arrayable`, `ArrayAccess`,
`JsonSerializable`, and `Jsonable`.

A subclass must implement one method:

```php
abstract public function validate(string $attribute, mixed $value, Closure $fail): void;
```

The base class adds `isValidFor(mixed $value): bool`, which calls `validate()`
with the literal attribute name `'attribute'` and a closure that records
failure. It returns `true` when the closure was never called. This is the method
the [service layer](/architecture/system-overview.md) uses.

The base class stores state in a `protected array $attributes` and exposes it
through `__get`/`__set`, array access, `toArray()`, and `toJson()`.

## The eight classes

Constructor parameter **names** matter: `make()` matches stored `parameters`
keys against them by name. See
[Rule Persistence and Instantiation](/features/rule-persistence-and-instantiation.md).

| Class            | Constructor parameters                                                                                                          |
| ---------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| `EnumRule`       | `string $enumClass`, `array $allowedValues = []`                                                                                |
| `OneOfRule`      | `array $allowedValues`                                                                                                          |
| `NoneOfRule`     | `array $disallowedValues`                                                                                                       |
| `DayOfWeekRule`  | `DayOfWeek $dayOfWeek`, `string $timezone = 'UTC'`                                                                              |
| `DaysOfWeekRule` | `array $daysOfWeek = [DayOfWeek::ALL]`, `string $timezone = 'UTC'`                                                              |
| `TimeOfDayRule`  | `?string $startTime = null`, `?string $endTime = null`, `?string $timezone = null`                                              |
| `TimesOfDayRule` | `array $timeRanges`                                                                                                             |
| `DayTimeRule`    | `DayOfWeek $dayOfWeek = DayOfWeek::ALL`, `string $startTime = '00:00'`, `string $endTime = '23:59'`, `string $timezone = 'UTC'` |

### EnumRule

Checks that a value is a valid case of `$enumClass`. When `$allowedValues` is
empty, any case of the enum passes. The constructor throws
`InvalidArgumentException` when `$enumClass` is not an enum, or when an entry in
`$allowedValues` does not resolve to a case of it.

`EnumRule` accepts a case, a backing value, or a case **name**, and converts
through `convertToEnum()` and `convertToEnumByName()`.

### DayTimeRule

A composite. It builds a `DayOfWeekRule` and a `TimeOfDayRule` internally and
calls both in `validate()`. Its `toArray()` merges the two children's arrays, so
the serialized form is flat.

With no arguments it accepts every day and the full `00:00`–`23:59` range —
in effect, always valid.

### Time rules

`TimeOfDayRule` takes a single start/end pair. `TimesOfDayRule` takes an array of
ranges and overrides `toArray()` to serialize them.

`TimeOfDayRule` defaults all three parameters to `null`, unlike `DayTimeRule`,
which supplies concrete string defaults.

# Examples

```php
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;

$rule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'America/New_York');

$rule->isValidFor('2026-09-07 10:30:00');
```

The same rule built from stored parameters:

```php
DayTimeRule::make([
    'dayOfWeek' => DayOfWeek::MONDAY,
    'startTime' => '09:00',
    'endTime'   => '17:00',
    'timezone'  => 'America/New_York',
]);
```

# Citations

- Verified 2026-09-04 against git HEAD — constructor signatures of all eight classes in `src/Rules/`
- Verified 2026-09-04 against git HEAD — `ValidationEngineRule::isValidFor()` passes the literal `'attribute'` name
- Verified 2026-09-04 against git HEAD — `DayTimeRule` composes `DayOfWeekRule` and `TimeOfDayRule` and merges `toArray()`
- `tests/Unit/Rules/`, `tests/Feature/Rules/`

# ValidationEngine User Guide

ValidationEngine is a Laravel library. It lets you keep validation rules in the
database instead of in code, so you can change what your application accepts
without a deployment.

This guide is for developers who install the package into a Laravel application.
For internals, see the [Technical Manual](../knowledge/index.md). For release and
operations tasks, see the [DevOps Guide](../devops/README.md).

## Requirements

- PHP 8.2 or later
- The `pdo` extension
- Laravel 12

## Install

```bash
composer require threeleaf/validation-engine
```

The service provider is discovered automatically. Run the migrations to create
the three tables:

```bash
php artisan migrate
```

This creates `v_rules`, `v_validators`, and `v_validator_rules`.

## Concepts

There are three pieces:

- A **rule** is one check. It names the field to look at, the rule class to
  apply, and that class's parameters.
- A **validator** is a named group of rules.
- A **validator rule** joins the two, and records the order and whether the link
  is active.

You run a validator against an array of data and get back `true` or `false`.

## Create a validator and a rule

```php
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

$validator = Validator::create([
    'name'          => 'StateAndTimeValidator',
    'description'   => 'Validates state and checks Monday business hours.',
    'active_status' => ActiveStatus::ACTIVE,
]);

$rule = Rule::create([
    'attribute'  => 'active_status',
    'rule_type'  => EnumRule::class,
    'parameters' => [
        'enumClass'     => ActiveStatus::class,
        'allowedValues' => [ActiveStatus::ACTIVE],
    ],
]);

ValidatorRule::create([
    'validator_id'  => $validator->validator_id,
    'rule_id'       => $rule->rule_id,
    'order_number'  => 1,
    'active_status' => ActiveStatus::ACTIVE,
]);
```

The keys inside `parameters` must match the rule class constructor's parameter
names. `EnumRule` takes `$enumClass` and `$allowedValues`, so those are the keys.

## Run a validator

Use `ValidatorService`. It accepts either the validator's UUID or its name.

```php
use ThreeLeaf\ValidationEngine\Services\ValidatorService;

$passes = app(ValidatorService::class)->runValidatorById(
    'StateAndTimeValidator',
    ['active_status' => 'Active'],
);
```

The result is `false` when a rule fails **and** when no active validator matches
the name. Set a validator's `active_status` to `Inactive` to switch it off.

## Available rule types

| Class            | Checks that a value…                                       |
| ---------------- | ---------------------------------------------------------- |
| `EnumRule`       | is a case of a given enum, optionally one of an allow list |
| `OneOfRule`      | is in an allowed list                                      |
| `NoneOfRule`     | is not in a disallowed list                                |
| `DayOfWeekRule`  | falls on a given day of the week                           |
| `DaysOfWeekRule` | falls on one of several days                               |
| `TimeOfDayRule`  | falls in a time range                                      |
| `TimesOfDayRule` | falls in one of several time ranges                        |
| `DayTimeRule`    | falls on a given day **and** in a time range               |

Constructor parameters for each are listed in
[Rule Types](../knowledge/features/rule-types.md).

## Use a rule directly

Rule classes implement Laravel's `ValidationRule` contract, so you can use one
without the database:

```php
use Illuminate\Support\Facades\Validator as LaravelValidator;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;

$laravelValidator = LaravelValidator::make(
    ['appointment' => '2026-09-07 10:30:00'],
    ['appointment' => [new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00')]],
);

$laravelValidator->passes();
```

## Write your own rule type

Extend `ValidationEngineRule` and implement `validate()`:

```php
use Closure;
use ThreeLeaf\ValidationEngine\Rules\ValidationEngineRule;

class PostalCodeRule extends ValidationEngineRule
{
    public function __construct(private readonly string $country = 'US')
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->country === 'US' && !preg_match('/^\d{5}(-\d{4})?$/', (string) $value)) {
            $fail('The :attribute is not a valid US postal code.');
        }
    }
}
```

You can then store `PostalCodeRule::class` in a rule row with
`{"country": "US"}` as its parameters.

Keep rule constructors free of I/O. They are built from stored data, so a
constructor with side effects runs whenever the rule is loaded.

## Optional HTTP API

The package ships a CRUD API in `routes/api.php`, but **does not register it**.
Include it yourself if you want it, and put it behind authentication:

```php
Route::middleware(['api', 'auth:sanctum', 'can:manage-validation'])
    ->prefix('api')
    ->group(base_path('vendor/threeleaf/validation-engine/routes/api.php'));
```

`manage-validation` is an example ability. Define it with `Gate::define()` or a
policy in your own application; until you do, `can:` denies every request.

These routes give full read and write access to your validation rules. Do not
expose them unauthenticated. See
[Route Protection](../knowledge/security/route-protection.md) and the
[API reference](../knowledge/api/index.md).

## License

GPL-3.0+. See [LICENSE](../../LICENSE).

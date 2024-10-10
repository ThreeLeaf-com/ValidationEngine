# ThreeLeaf.com Validation Engine

**A Laravel library for managing dynamic validation rules and configurations.**

## Overview

The `ValidationEngine` library provides a robust solution for managing validation rules and configurations in a dynamic manner. It allows you to define validators that group multiple rules and apply them based on various criteria, such as time and status.
This library is particularly useful for scenarios where validation logic needs to be customized or adjusted without directly modifying the codebase.

## Installation

Install the library via Composer:

```bash
composer require threeleaf/validation-engine
```

Run the migrations to create the necessary tables:

```bash
php artisan migrate
```

## Usage

### Setting Up Validators

To create a new validator and associate rules with it, use the `Validator` and `Rule` models:

```php
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;

// Create a new validator
$validator = Validator::create([
    'name' => 'StateAndTimeValidator',
    'description' => 'Validates state and checks time for Monday business hours.',
    'active_status' => ActiveStatus::Active->value,
]);

// Create a rule
$rule = Rule::create([
    'attribute' => 'state',
    'rule_type' => 'EnumRule',
    'parameters' => json_encode(['enum_class' => 'App\Enums\UsaState']),
]);

// Associate the rule with the validator
ValidatorRule::create([
    'validator_id' => $validator->validator_id,
    'rule_id' => $rule->rule_id,
    'order_number' => 1,
    'active_status' => ActiveStatus::Active->value,
]);
```

### Validating Data

Use the `Validator` model to apply the rules to a given input:

```php
use Illuminate\Support\Facades\Validator as LaravelValidator;

// Retrieve the validator
$validator = Validator::where('name', 'StateAndTimeValidator')->first();

// Apply validation rules
$data = ['state' => 'CA', 'date_time' => '2024-10-14 09:00:00'];
$rules = $validator->compileRules();

$laravelValidator = LaravelValidator::make($data, $rules);

if ($laravelValidator->fails()) {
    echo "Validation failed: " . $laravelValidator->errors()->first();
} else {
    echo "Validation passed!";
}
```

## Contributing

Contributions are welcome! Please submit a pull request or open an issue to discuss your ideas.

## License

This library is open-sourced software licensed under the [GPL-3.0+](https://www.gnu.org/licenses/gpl-3.0.html).

---

This `README.md` covers installation, usage, and the database structure for the `ValidationEngine` library, providing a clear guide for users. Let me know if you'd like to include any additional sections or make adjustments!

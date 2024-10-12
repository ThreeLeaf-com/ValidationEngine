# ThreeLeaf.com Validation Engine

**A Laravel library for managing dynamic validation rules and configurations.**

## Overview

The `ValidationEngine` library provides a robust solution for managing validation rules and configurations in a dynamic manner. It allows you to define validators that group multiple rules and apply them based on various criteria, such as time and status.
This library is particularly useful for scenarios where validation logic needs to be customized or adjusted without directly modifying the codebase.

# ValidationEngine

[![Latest Stable Version](https://poser.pugx.org/threeleaf/validation-engine/v/stable)](https://packagist.org/packages/threeleaf/validation-engine)
[![Build Status](https://github.com/ThreeLeaf-com/ValidationEngine/actions/workflows/tests.yaml/badge.svg?branch=main)](https://github.com/ThreeLeaf-com/ValidationEngine/actions)
![Coverage](./public/images/coverage-badge.svg)
[![License](https://poser.pugx.org/threeleaf/validation-engine/license)](https://packagist.org/packages/threeleaf/validation-engine)
[![PHP Version](https://img.shields.io/packagist/php-v/threeleaf/validation-engine)](https://packagist.org/packages/threeleaf/validation-engine)
[![GitHub last commit](https://img.shields.io/github/last-commit/ThreeLeaf-com/ValidationEngine)](https://github.com/ThreeLeaf-com/ValidationEngine/commits/main)
[![Total Downloads](https://poser.pugx.org/threeleaf/validation-engine/downloads)](https://packagist.org/packages/threeleaf/validation-engine)

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

## Miscellaneous

### OpenApi Documentation

OpenAPI documentation can be generated within the application using the command:

```bash
php util/generate-swagger.php
```

### Generate Coverage Badge

After running the tests with code coverage, run the following script to update the coverage badge:

```bash
php util/generate-coverage-badge.php
```

<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use ReflectionEnum;
use Throwable;

/**
 * Class EnumValidationRule
 *
 * A validation rule that checks if a given value is either present in a specified array of enum values
 * or, if no array is provided, that the value is a valid instance of the specified enum class.
 * It supports both matching based on the enum's value and matching based on the enum's name.
 *
 * Example usage:
 *
 * - Validate that a value is a specific instance of an enum:
 *   new EnumValidationRule(MyEnum::class)
 *
 * - Validate that a value is one of a subset of enum values:
 *   new EnumValidationRule(MyEnum::class, [MyEnum::FirstValue, MyEnum::SecondValue])
 *
 * Example with Validator:
 *
 * ```php
 * $rule = new EnumValidationRule(MyEnum::class, [MyEnum::FirstValue, MyEnum::SecondValue]);
 * $validator = Validator::make(
 *     ['enumValue' => 'FirstValue'],
 *     ['enumValue' => [$rule]]
 * );
 *
 * if ($validator->fails()) {
 *     echo "Validation failed: " . $validator->errors()->first('enumValue');
 * } else {
 *     echo "Validation passed.";
 * }
 * ```
 *
 * @package App\Rules
 */
class EnumRule implements ValidationRule
{
    protected string $enumClass;

    protected array $allowedValues;

    /**
     * Create a new rule instance.
     *
     * @param string $enumClass     The fully qualified class name of the enum.
     * @param array  $allowedValues Optional array of allowed enum values.
     *
     * @throws InvalidArgumentException If the provided class is not a valid enum.
     */
    public function __construct(string $enumClass, array $allowedValues = [])
    {
        if (!enum_exists($enumClass)) {
            throw new InvalidArgumentException("The class $enumClass is not a valid enum.");
        }

        $this->enumClass = $enumClass;
        $this->allowedValues = $allowedValues;
    }

    /**
     * Validate the given attribute.
     *
     * @param string  $attribute The name of the attribute being validated.
     * @param mixed   $value     The value of the attribute.
     * @param Closure $fail      The closure to call if validation fails.
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        /* If the value is a string, attempt to convert it to an enum instance by value or name */
        if (is_string($value)) {
            $value = $this->getEnumFromValueOrName($value);
        }

        /* If the value is null after conversion, it means it's not a valid enum instance */
        if ($value === null) {
            $fail("The $attribute is not a valid instance of $this->enumClass.");
            return;
        }

        /* If allowed values are provided, check that the value is in the array */
        if (!empty($this->allowedValues)) {
            if (!in_array($value, $this->allowedValues, true)) {
                $fail("The $attribute must be one of the allowed values.");
            }
            return;
        }

        /* No specific allowed values, just ensure it's a valid enum instance */
        if (!$value instanceof $this->enumClass) {
            $fail("The $attribute is not a valid instance of $this->enumClass.");
        }
    }

    /**
     * Attempts to convert a string to an enum instance using the value or the name.
     *
     * @param string $input The input string to match.
     *
     * @return mixed The matched enum instance, or null if not found.
     */
    protected function getEnumFromValueOrName(string $input): mixed
    {
        /** @noinspection PhpUndefinedMethodInspection tryFrom is a standard enum function. */
        $enumInstance = $this->enumClass::tryFrom($input);
        if ($enumInstance !== null) {
            return $enumInstance;
        }

        /* If no match by value, try matching by name */
        try {
            $reflection = new ReflectionEnum($this->enumClass);
            foreach ($reflection->getCases() as $case) {
                if ($case->getName() === $input) {
                    return $case->getValue();
                }
            }
        } catch (Throwable) {
            /* Ignore */
        }

        return null;
    }
}

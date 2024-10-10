<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use ReflectionEnum;
use Throwable;
use UnitEnum;

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
 * - Validate that a value is one of a subset using enum names:
 *   new EnumValidationRule(MyEnum::class, ['EnumValue', 'EnumName', MyEnum::NAME_TWO])
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
     * @param string       $enumClass     The fully qualified class name of the enum.
     * @param array<mixed> $allowedValues Optional array of allowed enum values, names, or instances.
     *
     * @throws InvalidArgumentException If the provided class is not a valid enum or an allowed value does not represent a valid enum.
     */
    public function __construct(string $enumClass, array $allowedValues = [])
    {
        if (!enum_exists($enumClass)) {
            throw new InvalidArgumentException("The class $enumClass is not a valid enum.");
        }

        $this->enumClass = $enumClass;
        $this->allowedValues = $this->convertToEnumArray($allowedValues);
        if (count($allowedValues) != count($this->allowedValues)) {
            $allowedValuesString = implode(', ', array_map(function ($value) {
                return (string)$value;
            }, $allowedValues));
            throw new InvalidArgumentException("At least one of [$allowedValuesString] is not a valid instance of $this->enumClass.");
        }
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
        $parsedValue = $this->convertToEnum($value);

        if ($parsedValue === null) {
            $fail("The $attribute is not a valid instance of $this->enumClass.");
        } elseif (!empty($this->allowedValues) && !in_array($parsedValue, $this->allowedValues, true)) {
            $fail("The $attribute must be one of the allowed values.");
        } elseif (!$parsedValue instanceof $this->enumClass) {
            $fail("The $attribute is not a valid instance of $this->enumClass.");
        }
    }

    /**
     * Attempts to convert a string to an enum instance using the value or the name.
     *
     * @param mixed $input The input to convert.
     *
     * @return UnitEnum|null The matched enum instance, or null if not found.
     */
    public function convertToEnum(mixed $input): ?UnitEnum
    {
        if ($input instanceof $this->enumClass || $input === null) {
            $convertedEnum = $input;
        } else {
            /** @noinspection PhpUndefinedMethodInspection tryFrom is a standard enum function. */
            $enumInstance = $this->enumClass::tryFrom($input);

            if ($enumInstance !== null) {
                $convertedEnum = $enumInstance;
            } else {
                $convertedEnum = $this->convertToEnumByName($input);
            }
        }

        return $convertedEnum;
    }

    /**
     * Convert an array of values to an array of enum instances.
     *
     * @param array<mixed> $inputs The inputs to convert.
     *
     * @return array<UnitEnum> The array of valid enum instances.
     */
    public function convertToEnumArray(array $inputs): array
    {
        $enumInstances = [];

        foreach ($inputs as $input) {
            $enumInstance = $this->convertToEnum($input);

            if ($enumInstance) {
                $enumInstances[] = $enumInstance;
            }
        }

        return $enumInstances;
    }

    /**
     * Attempts to convert a string to an enum instance by its name.
     *
     * @param mixed $input The input string to match.
     *
     * @return UnitEnum|null The matched enum instance, or null if not found.
     */
    protected function convertToEnumByName(mixed $input): ?UnitEnum
    {
        $result = null;

        try {
            $reflection = new ReflectionEnum($this->enumClass);
            foreach ($reflection->getCases() as $case) {
                if ($case->getName() === $input) {
                    $result = $case->getValue();
                    break;
                }
            }
        } catch (Throwable) {
            /* Ignore exceptions. */
        }

        return $result;
    }
}

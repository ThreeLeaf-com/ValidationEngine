<?php
/** @noinspection PhpMultipleClassDeclarationsInspection */

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use InvalidArgumentException;
use ReflectionEnum;
use ReflectionException;
use UnitEnum;

/**
 * A validation rule that checks if a given value is either present in a specified array of enum values
 * or, if no array is provided, that the value is a valid instance of the specified enum class.
 * It supports both matching based on the enum's value and matching based on the enum's name.
 *
 * @property  string $enumClass     The fully qualified class name of the enum.
 * @property array   $allowedValues The array of allowed enum values, names, or instances.
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
 */
class EnumRule extends ValidationEngineRule
{
    /**
     * Create a new rule instance.
     *
     * @param string $enumClass     The fully qualified class name of the enum.
     * @param array  $allowedValues Optional array of allowed enum values, names, or instances.
     *
     * @throws InvalidArgumentException If the provided class is not a valid enum or an allowed value does not represent a valid enum.
     */
    public function __construct(string $enumClass, array $allowedValues = [])
    {
        if (!enum_exists($enumClass)) {
            throw new InvalidArgumentException("The class $enumClass is not a valid enum.");
        }

        $allowedValues = array_filter($allowedValues, function ($value) {
            /* Remove null and whitespace entries automatically */
            return !is_null($value) && !(is_string($value) && trim($value) === '');
        });

        $this->enumClass = $enumClass;
        $this->allowedValues = $this->convertToEnumArray($allowedValues);
        if (count($allowedValues) > count($this->allowedValues)) {
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
        } elseif (empty($this->attributes['allowedValues'])) {
            $fail("No $this->enumClass values set.");
        } elseif (!in_array($parsedValue, $this->allowedValues, true)) {
            $fail("The $attribute must be one of the allowed values.");
        }
    }

    /**
     * Convert the given input to an enum instance using the value or name.
     *
     * @param mixed $input The input to convert.
     *
     * @return UnitEnum|null The matched enum instance, or null if not found.
     */
    public function convertToEnum(mixed $input): ?UnitEnum
    {
        if (empty($input)) {
            $convertedEnum = null;
        } elseif ($input instanceof $this->enumClass) {
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
     * Attempts to convert a string to an enum instance by its name.
     *
     * @param string $input The input string to match.
     *
     * @return UnitEnum|null The matched enum instance, or null if not found.
     */
    public function convertToEnumByName(string $input): ?UnitEnum
    {
        $result = null;
        $input = strtoupper($input);

        try {
            $reflection = new ReflectionEnum($this->enumClass);
            foreach ($reflection->getCases() as $case) {
                if ($case->getName() === $input) {
                    $result = $case->getValue();
                    break;
                }
            }
        } catch (ReflectionException) {
            /* Ignore exceptions. */
        }

        return $result;
    }

    /**
     * Convert an array of values to an array of enum instances.
     *
     * @param array $inputs The inputs to convert.
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
}

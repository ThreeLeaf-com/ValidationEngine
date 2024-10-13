<?php

namespace ThreeLeaf\ValidationEngine\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * This custom cast is used to validate that a given string represents a valid class name
 * that either extends, implements, or uses a specified class, interface, or trait.
 *
 * The primary use case is for casting class names in Eloquent models to ensure that
 * they conform to a particular type, either by class inheritance, interface implementation,
 * or trait usage.
 *
 * Example usage in a Laravel Eloquent Model:
 *
 * ```php
 * use App\Casts\ClassCast;
 * use Illuminate\Contracts\Validation\ValidationRule;
 *
 * class Rule extends Model
 * {
 *     protected $casts = [
 *         'rule_type' => ClassCast::class . ':' . ValidationRule::class,  // Validate that rule_type is a ValidationRule implementation.
 *     ];
 * }
 * ```
 *
 * This class performs the following checks:
 * - Validates if the provided class exists.
 * - Checks if the provided class is a subclass of the specified class.
 * - Checks if the provided class implements the specified interface.
 * - Checks if the provided class uses the specified trait.
 *
 * Throws an `InvalidArgumentException` if the provided class does not meet the validation criteria.
 *
 * Constructor parameters:
 * - `$classType`: The fully qualified class name of the class, interface, or trait that the value should extend, implement, or use.
 *
 */
class ClassCast implements CastsAttributes
{
    protected string $classType;

    /**
     * ClassCast constructor.
     *
     * @param string $classType The fully qualified name of the class, interface, or trait.
     */
    public function __construct(string $classType)
    {
        if (!class_exists($classType) && !interface_exists($classType) && !trait_exists($classType)) {
            throw new InvalidArgumentException("$classType is not a valid class, interface, or trait.");
        }

        $this->classType = $classType;
    }

    /**
     * Retrieve the given value when retrieving from the database.
     *
     * @param Model  $model
     * @param string $key
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return mixed
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }

    /**
     * Prepare the given value for storage in the database.
     *
     * @param Model  $model
     * @param string $key
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return mixed
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (!class_exists($value)) {
            throw new InvalidArgumentException("$value is not a valid class.");
        }

        if (!is_subclass_of($value, $this->classType) && !in_array($this->classType, class_implements($value), true) && !in_array($this->classType, class_uses($value), true)) {
            throw new InvalidArgumentException("$value does not extend, implement, or use $this->classType.");
        }

        return $value;
    }
}

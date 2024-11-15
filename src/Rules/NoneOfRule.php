<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use InvalidArgumentException;

/**
 * A validation rule that ensures a value is NOT present in a predefined array of restricted values.
 *
 * @property array $restrictedValues The array of restricted values.
 */
class NoneOfRule extends ValidationEngineRule
{
    /** @var array The restricted values */
    private array $restrictedValues;

    /**
     * Constructor for the NoneOfRule.
     *
     * @param array $restrictedValues An array of restricted values for the rule.
     */
    public function __construct(array $restrictedValues)
    {
        if (empty($restrictedValues)) {
            throw new InvalidArgumentException('The restricted values array cannot be empty.');
        }

        $this->restrictedValues = $restrictedValues;
    }

    /**
     * Validate the given value against the rule.
     *
     * @param string  $attribute The name of the attribute under validation.
     * @param mixed   $value     The value of the attribute under validation.
     * @param Closure $fail      A callback function to report validation failures.
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (in_array($value, $this->restrictedValues, true)) {
            $fail("The $attribute must not be any of the restricted values: " . implode(', ', $this->restrictedValues) . '.');
        }
    }
}

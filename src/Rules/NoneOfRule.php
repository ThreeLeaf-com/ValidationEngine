<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use InvalidArgumentException;

/**
 * A validation rule that checks if a given value is NOT present in a predefined array of disallowed values.
 * The disallowed values may include scalars, strings, or regular expressions.
 *
 * @property array $disallowedValues The array of disallowed values (scalars or regex strings).
 */
class NoneOfRule extends ValidationEngineRule
{

    /**
     * Constructor for the NoneOfRule.
     *
     * @param array $disallowedValues An array of disallowed values for the rule.
     *                                Regular expressions must be valid and properly delimited.
     */
    public function __construct(array $disallowedValues)
    {
        if (empty($disallowedValues)) {
            throw new InvalidArgumentException('The disallowed values array cannot be empty.');
        }

        $this->disallowedValues = $disallowedValues;
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
        foreach ($this->disallowedValues as $disallowedValue) {
            if (is_string($disallowedValue) && @preg_match($disallowedValue, '') !== false) {
                /* Regular Expression Check */
                if (preg_match($disallowedValue, $value)) {
                    $fail("The $attribute must not match the disallowed pattern: $disallowedValue.");
                    return;
                }
            } elseif ($value === $disallowedValue) {
                /* Scalar value matched */
                $fail("The $attribute must not be one of the disallowed values: " . var_export($disallowedValue, true) . '.');
                return;
            }
        }
    }
}

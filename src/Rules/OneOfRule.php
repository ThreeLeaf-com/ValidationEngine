<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use InvalidArgumentException;

/**
 * A validation rule that checks if a given value is present in a predefined array of allowed values.
 * The allowed values may include scalars, strings, or valid regular expressions.
 *
 * @property array $allowedValues The array of allowed values.
 */
class OneOfRule extends ValidationEngineRule
{
    /** @var array The allowed values (scalars or regex strings). */
    private array $allowedValues;

    /**
     * Constructor for the OneOfRule.
     *
     * @param array $allowedValues An array of allowed values for the rule.
     *                             Regular expressions must be valid and properly delimited.
     */
    public function __construct(array $allowedValues)
    {
        if (empty($allowedValues)) {
            throw new InvalidArgumentException('The allowed values array cannot be empty.');
        }

        $this->allowedValues = $allowedValues;
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
        $isMatched = false;
        foreach ($this->allowedValues as $allowedValue) {
            if (is_string($allowedValue) && @preg_match($allowedValue, '') !== false) {
                /* Regular Expression Check */
                if (preg_match($allowedValue, $value)) {
                    $isMatched = true;
                    break;
                }
            } elseif ($value === $allowedValue) {
                /* Scalar value matched */
                $isMatched = true;
                break;
            }
        }

        if (!$isMatched) {
            $fail("The $attribute must be one of the allowed values: " . implode(', ', $this->allowedValues) . '.');
        }
    }
}

<?php

namespace Tests\Unit\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Rules\OneOfRule;

class OneOfRuleTest extends TestCase
{
    /**
     * Test the validation passes for values in the allowed array.
     */
    public function testValidationPassesForAllowedValues(): void
    {
        $allowedValues = ['apple', 'banana', 'cherry'];
        $rule = new OneOfRule($allowedValues);

        $attribute = 'fruit';
        $value = 'banana';

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for allowed value.');
    }

    /**
     * Test the validation fails for values not in the allowed array.
     */
    public function testValidationFailsForDisallowedValues(): void
    {
        $allowedValues = ['apple', 'banana', 'cherry'];
        $rule = new OneOfRule($allowedValues);

        $attribute = 'fruit';
        $value = 'orange';

        $rule->validate($attribute, $value, function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must be one of the allowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }

    /**
     * Test the rule throws an exception when initialized with an empty allowed values array.
     */
    public function testThrowsExceptionForEmptyAllowedValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The allowed values array cannot be empty.');

        new OneOfRule([]);
    }
}

<?php

namespace Tests\Unit\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Rules\NoneOfRule;

class NoneOfRuleTest extends TestCase
{
    /**
     * Test the validation passes for values not in the restricted array.
     */
    public function testValidationPassesForAllowedValues(): void
    {
        $restrictedValues = ['admin', 'root', 'superuser'];
        $rule = new NoneOfRule($restrictedValues);

        $attribute = 'username';
        $value = 'john';

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for value not in restricted list.');
    }

    /**
     * Test the validation fails for values in the restricted array.
     */
    public function testValidationFailsForRestrictedValues(): void
    {
        $restrictedValues = ['admin', 'root', 'superuser'];
        $rule = new NoneOfRule($restrictedValues);

        $attribute = 'username';
        $value = 'admin';

        $rule->validate($attribute, $value, function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be any of the restricted values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }

    /**
     * Test the rule throws an exception when initialized with an empty restricted values array.
     */
    public function testThrowsExceptionForEmptyRestrictedValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The restricted values array cannot be empty.');

        new NoneOfRule([]);
    }
}

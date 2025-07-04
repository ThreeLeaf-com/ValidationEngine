<?php

namespace Tests\Unit\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ThreeLeaf\ValidationEngine\Rules\OneOfRule;

/** Test {@link OneOfRule}. */
class OneOfRuleTest extends TestCase
{
    /** {@link OneOfRule::validate()} passes for values in the allowed array. */
    #[Test]
    public function validationPassesForAllowedValues(): void
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

    /** {@link OneOfRule::validate()} fails for values not in the allowed array. */
    #[Test]
    public function validationFailsForDisallowedValues(): void
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

    /** {@link OneOfRule::validate()} passes for scalar values in the allowed array. */
    #[Test]
    public function validationPassesForAllowedScalarValues(): void
    {
        $allowedValues = ['apple', 'banana', 123, 45.67];
        $rule = new OneOfRule($allowedValues);

        $attribute = 'test_attribute';
        $value = 123;

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for allowed scalar value.');
    }

    /** {@link OneOfRule::validate()} passes for values matching allowed regex patterns. */
    #[Test]
    public function validationPassesForRegexMatches(): void
    {
        $allowedValues = ['/^[a-z]{3}-[0-9]{2}$/', '/^test-[0-9]+$/'];
        $rule = new OneOfRule($allowedValues);

        $attribute = 'pattern';
        $value = 'abc-12';

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for value matching allowed regex.');
    }

    /** {@link OneOfRule::validate()} fails for values not in the allowed array or regex. */
    #[Test]
    public function validationFailsForDisallowedValues2(): void
    {
        $allowedValues = ['apple', '/^test-[0-9]+$/'];
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

    /** {@link OneOfRule::__construct()} throws an exception when initialized with an empty array. */
    #[Test]
    public function constructorThrowsExceptionForEmptyValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The allowed values array cannot be empty.');

        new OneOfRule([]);
    }

    /** Ensure invalid regex strings in allowed array are treated as scalars, not regex, and do not trigger errors. */
    #[Test]
    public function validationHandlesInvalidRegexStringsSafely(): void
    {
        $allowedValues = ['not_a_regex', 'abc[123', 'foo\\bar'];
        $rule = new OneOfRule($allowedValues);

        $attribute = 'test';

        // Should pass for exact match
        $rule->validate($attribute, 'not_a_regex', function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });
        $rule->validate($attribute, 'abc[123', function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });
        $rule->validate($attribute, 'foo\\bar', function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        // Should fail for non-matching value
        $rule->validate($attribute, 'something_else', function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must be one of the allowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }
}

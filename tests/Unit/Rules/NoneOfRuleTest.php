<?php

namespace Tests\Unit\Rules;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Rules\NoneOfRule;

/** Test {@link NoneOfRule}. */
class NoneOfRuleTest extends TestCase
{
    /** {@link NoneOfRule::validate()} passes for values not in the disallowed array. */
    #[Test]
    public function validationPassesForAllowedValues(): void
    {
        $disallowedValues = ['admin', 'root', 'superuser'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'username';
        $value = 'john';

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for value not in disallowed list.');
    }

    /** Test {@link NoneOfRule::validate()} fails for values in the disallowed array. */
    #[Test]
    public function validationFailsForDisallowedValues(): void
    {
        $disallowedValues = ['admin', 'root', 'superuser'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'username';
        $value = 'admin';

        $rule->validate($attribute, $value, function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be one of the disallowed values: 'admin'.",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }

    /** Test {@link NoneOfRule::validate()} passes for values not in the disallowed array. */
    #[Test]
    public function validationPassesForAllowedValues2(): void
    {
        $disallowedValues = ['admin', 'root', '/^restricted-[0-9]+$/'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'username';
        $value = 'john';

        $rule->validate($attribute, $value, function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });

        $this->assertTrue(true, 'Validation passed for value not in disallowed list.');
    }

    /** Test {@link NoneOfRule::validate()} fails for values in the disallowed array. */
    #[Test]
    public function validationFailsForDisallowedScalarValues(): void
    {
        $disallowedValues = ['admin', 'root'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'username';
        $value = 'admin';

        $rule->validate($attribute, $value, function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be one of the disallowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }

    /** Test {@link NoneOfRule::validate()} fails for values matching disallowed regex patterns. */
    #[Test]
    public function validationFailsForRegexMatches(): void
    {
        $disallowedValues = ['/^restricted-[0-9]+$/', '/^admin-.+$/'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'username';
        $value = 'restricted-123';

        $rule->validate($attribute, $value, function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not match the disallowed pattern",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
    }

    /** Test {@link NoneOfRule::__construct()} throws an exception when initialized with an empty array. */
    #[Test]
    public function constructorThrowsExceptionForEmptyValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The disallowed values array cannot be empty.');

        new NoneOfRule([]);
    }

    /** Ensure invalid regex strings in disallowed array are treated as scalars, not regex, and do not trigger errors. */
    #[Test]
    public function validationHandlesInvalidRegexStringsSafely(): void
    {
        $disallowedValues = ['not_a_regex', 'abc[123', 'foo\\bar'];
        $rule = new NoneOfRule($disallowedValues);

        $attribute = 'test';

        // Should fail for exact match
        $rule->validate($attribute, 'not_a_regex', function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be one of the disallowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
        $rule->validate($attribute, 'abc[123', function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be one of the disallowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });
        $rule->validate($attribute, 'foo\\bar', function (string $message) use ($attribute) {
            $this->assertStringContainsString(
                "The $attribute must not be one of the disallowed values",
                $message,
                'Validation error message does not match the expected output.'
            );
        });

        // Should pass for non-matching value
        $rule->validate($attribute, 'something_else', function (string $message) {
            $this->fail("Validation failed unexpectedly with message: $message");
        });
    }
}

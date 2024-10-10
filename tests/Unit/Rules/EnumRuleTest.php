<?php

namespace Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** @covers {@link EnumRule}. */
class EnumRuleTest extends TestCase
{
    /**
     * Test that a valid state abbreviation passes.
     */
    public function testValidStatePasses()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid state abbreviation. */
        $rule->validate('state', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when a valid state abbreviation is provided.');
        $this->assertTrue($message == '');
    }

    /**
     * Test that an invalid state abbreviation fails.
     */
    public function testInvalidStateFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'XX' is not a valid state abbreviation. */
        $rule->validate('state', 'XX', $fail);

        $this->assertTrue($failed, 'Validation should fail when an invalid state abbreviation is provided.');
        $this->assertStringContainsString('The state is not a valid instance of', $message);
    }

    /**
     * Test that the value is in the allowed subset of states.
     */
    public function testAllowedSubsetPasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::Monday, DayOfWeek::Wednesday]);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* 'Monday' should be in the allowed subset. */
        $rule->validate('state', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when the value is within the allowed subset.');
        $this->assertTrue($message == '');
    }

    /**
     * Test that a value outside the allowed subset fails.
     */
    public function testOutsideAllowedSubsetFails()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::Monday, DayOfWeek::Tuesday]);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* 'Tuesday' is not in the allowed subset. */
        $rule->validate('day', 'Wednesday', $fail);

        $this->assertTrue($failed, 'Validation should fail when the value is outside the allowed subset.');
        $this->assertStringContainsString('The day must be one of the allowed values.', $message);
    }

    /**
     * Test that a null value fails validation.
     */
    public function testNullValueFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        $rule->validate('state', null, $fail);

        $this->assertTrue($failed, 'Validation should fail when a null value is provided.');
        $this->assertStringContainsString('The state is not a valid instance of', $message);
    }

    /**
     * Test that a valid state name passes.
     */
    public function testValidStateNamePasses()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid state name corresponding to DayOfWeek::Monday */
        $rule->validate('day', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when a valid day name is provided.');
        $this->assertTrue($message == '');
    }

    /**
     * Test that an invalid state name fails.
     */
    public function testInvalidStateNameFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Atlantis' is not a valid state name. */
        $rule->validate('state', 'Atlantis', $fail);

        $this->assertTrue($failed, 'Validation should fail when an invalid state name is provided.');
        $this->assertStringContainsString('The state is not a valid instance of', $message);
    }
}

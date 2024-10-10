<?php

namespace Tests\Unit\Rules;

use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link EnumRule}. */
class EnumRuleTest extends TestCase
{
    /** @test that a valid enum abbreviation passes. */
    public function testValidEnumPasses()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid enum abbreviation. */
        $rule->validate('enum', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when a valid enum abbreviation is provided.');
        $this->assertTrue($message == '');
    }

    /** @test that an invalid enum abbreviation fails. */
    public function testInvalidEnumFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'XX' is not a valid enum abbreviation. */
        $rule->validate('enum', 'XX', $fail);

        $this->assertTrue($failed, 'Validation should fail when an invalid enum abbreviation is provided.');
        $this->assertStringContainsString('The enum is not a valid instance of', $message);
    }

    /** @test that the value is in the allowed subset of enums. */
    public function testAllowedSubsetPasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY, DayOfWeek::WEDNESDAY]);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* 'Monday' should be in the allowed subset. */
        $rule->validate('enum', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when the value is within the allowed subset.');
        $this->assertTrue($message == '');
    }

    /** @test that a value outside the allowed subset fails. */
    public function testOutsideAllowedSubsetFails()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY, DayOfWeek::TUESDAY]);
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

    /** @test that a null value fails validation. */
    public function testNullValueFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        $rule->validate('enum', null, $fail);

        $this->assertTrue($failed, 'Validation should fail when a null value is provided.');
        $this->assertStringContainsString('The enum is not a valid instance of', $message);
    }

    /** @test that a valid enum name passes. */
    public function testValidEnumNamePasses()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid enum name corresponding to DayOfWeek::Monday */
        $rule->validate('day', 'Monday', $fail);

        $this->assertFalse($failed, 'Validation should pass when a valid day name is provided.');
        $this->assertTrue($message == '');
    }

    /** @test that an invalid enum name fails. */
    public function testInvalidEnumNameFails()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Atlantis' is not a valid enum name. */
        $rule->validate('enum', 'Atlantis', $fail);

        $this->assertTrue($failed, 'Validation should fail when an invalid enum name is provided.');
        $this->assertStringContainsString('The enum is not a valid instance of', $message);
    }
}

<?php

namespace Tests\Unit\Rules;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link EnumRule}. */
class EnumRuleTest extends TestCase
{

    /** {@link EnumRule::validate()} fails when allowed enums is empty. */
    #[Test]
    public function validateNoneAllowed()
    {
        $rule = new EnumRule(DayOfWeek::class);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid enum abbreviation. */
        $rule->validate('enum', 'tuesday', $fail);

        $this->assertTrue($failed, 'Validation should fail when no enums are allowed.');
        $this->assertTrue($message == 'No ThreeLeaf\ValidationEngine\Enums\DayOfWeek values set.');
    }

    /** Test that a valid enum abbreviation passes. */
    #[Test]
    public function validEnumPasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::TUESDAY]);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        /* Assume 'Monday' is a valid enum abbreviation. */
        $rule->validate('enum', 'tuesday', $fail);

        $this->assertFalse($failed, 'Validation should pass when a valid enum abbreviation is provided.');
        $this->assertTrue($message == '');
    }

    /** Test that an invalid enum abbreviation fails. */
    #[Test]
    public function invalidEnumFails()
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

    /** Test that the value is in the allowed subset of enums. */
    #[Test]
    public function allowedSubsetPasses()
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

    /** Test that a value outside the allowed subset fails. */
    #[Test]
    public function outsideAllowedSubsetFails()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY, DayOfWeek::TUESDAY]);
        $failed = false;
        $message = '';

        $fail = function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };

        $this->assertFalse($rule->isValidFor(DayOfWeek::SATURDAY));

        /* 'Wednesday' is not in the allowed subset. */
        $rule->validate('day', 'Wednesday', $fail);

        $this->assertTrue($failed, 'Validation should fail when the value is outside the allowed subset.');
        $this->assertStringContainsString('The day must be one of the allowed values.', $message);
    }

    /** Test that a null value fails validation. */
    #[Test]
    public function nullValueFails()
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

    /** Test that a valid enum name passes. */
    #[Test]
    public function validEnumNamePasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY]);
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

    /** Test that an invalid enum name fails. */
    #[Test]
    public function invalidEnumNameFails()
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

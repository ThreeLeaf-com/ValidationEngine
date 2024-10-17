<?php

namespace Tests\Unit\Rules;

use Closure;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;

/** Test {@link DayTimeRule}. */
class DayTimeRuleTest extends TestCase
{
    /** @test the rule for a specific day (e.g., Monday). */
    public function testSpecificDayPasses()
    {
        $rule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'UTC');
        $failed = false;
        $message = '';

        /* Test with a Monday within the time range */
        $rule->validate('date_time', '2024-10-14 10:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass when date is within the time range on Monday.');

        /* Test with a Monday outside the time range */
        $failed = false;
        $rule->validate('date_time', '2024-10-14 18:00:00', $this->createFailClosure($failed, $message));
        $this->assertTrue($failed, 'Validation should fail when time is outside the allowed range.');
        $this->assertStringContainsString('The date_time is not within the allowed time window', $message);

        /* Test with a non-Monday */
        $failed = false;
        $rule->validate('date_time', '2024-10-15 10:00:00', $this->createFailClosure($failed, $message));
        $this->assertTrue($failed, 'Validation should fail when the day is not Monday.');
        $this->assertStringContainsString('The date_time is not within the allowed day-of-week', $message);
    }

    /** @test the rule for "Weekend". */
    public function testWeekendPasses()
    {
        $rule = new DayTimeRule(DayOfWeek::WEEKEND, '08:00', '20:00', 'America/New_York');
        $failed = false;
        $message = '';

        /* Test with a Saturday within the time range */
        $rule->validate('date_time', '2024-10-12 12:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass when date is within the time range on Saturday.');

        /* Test with a Sunday within the time range */
        $failed = false;
        $rule->validate('date_time', '2024-10-13 19:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass when date is within the time range on Sunday.');

        /* Test with a Monday (not a weekend) */
        $failed = false;
        $rule->validate('date_time', '2024-10-14 19:00:00', $this->createFailClosure($failed, $message));
        $this->assertTrue($failed, 'Validation should fail when the day is not a weekend.');
        $this->assertStringContainsString('The date_time is not within the allowed day-of-week', $message);
    }

    /** @test the rule for "Weekday". */
    public function testWeekdayPasses()
    {
        $rule = new DayTimeRule(DayOfWeek::WEEKDAY, '09:00', '17:00', 'UTC');
        $failed = false;
        $message = '';

        /* Test with a Friday within the time range */
        $rule->validate('date_time', '2024-10-11 10:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass when date is within the time range on a weekday.');

        /* Test with a Saturday (not a weekday) */
        $failed = false;
        $rule->validate('date_time', '2024-10-12 10:00:00', $this->createFailClosure($failed, $message));
        $this->assertTrue($failed, 'Validation should fail when the day is not a weekday.');
        $this->assertStringContainsString('The date_time is not within the allowed day-of-week', $message);
    }

    /** @test the rule for "All" days. */
    public function testAllDaysPasses()
    {
        $rule = new DayTimeRule(DayOfWeek::ALL, '00:00', '23:59', 'UTC');
        $failed = false;
        $message = '';

        /* Should pass on any day */
        $rule->validate('date_time', '2024-10-12 15:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass on any day when set to "All".');

        $rule->validate('date_time', '2024-10-13 01:00:00', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass on any day when set to "All".');
    }

    /** @test the rule defaults to the current time when no value is provided. */
    public function testDefaultsToNow()
    {
        $rule = new DayTimeRule(DayOfWeek::ALL, '00:00', '23:59', 'UTC');
        $failed = false;
        $message = '';

        /* Validate without a value, should use current time */
        $rule->validate('date_time', null, $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should use the current time when no value is provided and pass.');
    }

    /** @test the rule respects timezones. */
    public function testRespectsTimezone()
    {
        $rule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'America/New_York');
        $failed = false;
        $message = '';

        /* Should pass, as it's 09:00 in New York (13:00 UTC) */
        $rule->validate('date_time', '2024-10-14 13:00:00 UTC', $this->createFailClosure($failed, $message));
        $this->assertFalse($failed, 'Validation should pass when the time is within the range in the specified timezone.');

        /* Should fail, as it's outside the time range in New York (23:00 UTC is 19:00 in New York) */
        $failed = false;
        $rule->validate('date_time', '2024-10-14 23:00:00 UTC', $this->createFailClosure($failed, $message));
        $this->assertTrue($failed, 'Validation should fail when the time is outside the range in the specified timezone.');
        $this->assertStringContainsString('The date_time is not within the allowed time window', $message);
    }

    /** Helper method to create a fail closure that captures messages. */
    protected function createFailClosure(&$failed, &$message): Closure
    {
        return function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };
    }
}

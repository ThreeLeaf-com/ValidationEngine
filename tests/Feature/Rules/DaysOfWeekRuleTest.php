<?php

namespace Tests\Feature\Rules;

use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DaysOfWeekRule;

/** Test {@link DaysOfWeekRule}. */
class DaysOfWeekRuleTest extends TestCase
{
    /** Test that a valid date falls on one of the allowed days of the week.*/
    #[Test]
    public function positiveValidation()
    {
        $rule = new DaysOfWeekRule([DayOfWeek::MONDAY, DayOfWeek::WEDNESDAY, DayOfWeek::FRIDAY]);

        $validator = Validator::make(
            ['date' => '2024-10-16'], /* Wednesday */
            ['date' => [$rule]]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass for a valid day (Wednesday).');
    }

    /** Test that a date falling on a day outside the allowed days fails validation. */
    #[Test]
    public function negativeValidation()
    {
        $rule = new DaysOfWeekRule([DayOfWeek::MONDAY, DayOfWeek::WEDNESDAY, DayOfWeek::FRIDAY]);

        $validator = Validator::make(
            ['date' => '2024-10-17'], /* Thursday */
            ['date' => [$rule]]
        );

        $this->assertFalse($validator->passes(), 'Validation should fail for an invalid day (Thursday).');
        $this->assertEquals(
            'The date does not match any of the allowed days of the week.',
            $validator->errors()->first('date'),
            'The error message should match the expected validation failure message.'
        );
    }

    /**Test  the instantiation of the DaysOfWeekRule class.*/
    #[Test]
    public function instantiateRule()
    {
        $rule = new DaysOfWeekRule([DayOfWeek::MONDAY, DayOfWeek::WEDNESDAY, DayOfWeek::FRIDAY], 'America/New_York');

        $this->assertInstanceOf(DaysOfWeekRule::class, $rule, 'The rule should be an instance of DaysOfWeekRule.');

        $this->assertEquals([DayOfWeek::MONDAY, DayOfWeek::WEDNESDAY, DayOfWeek::FRIDAY], $rule->daysOfWeek, 'Days of the week should match.');
        $this->assertEquals('America/New_York', $rule->timezone, 'Timezone should match.');
    }

    /** Test that validation fails when $value is null. */
    #[Test]
    public function validateFailsWithNullValue()
    {
        $rule = new DaysOfWeekRule([DayOfWeek::ALL]);

        $validator = Validator::make(
            ['date' => null],
            ['date' => [$rule]]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass for all days.');
    }
}

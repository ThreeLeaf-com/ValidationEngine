<?php

namespace Tests\Feature\Rules;

use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link DayTimeRule} and {@link EnumRule} combined. */
class EnumAndDayTimeRuleTest extends TestCase
{
    /** @test that a valid dayOfWeek and time range combination passes validation. */
    public function testValidDayOfWeekAndTimePasses()
    {
        $dayOfWeekRule = new EnumRule(DayOfWeek::class);
        $dayTimeRule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'America/New_York');

        $validator = Validator::make(
            [
                'day' => 'Monday',
                'date_time' => '2024-10-14 13:00:00 UTC',
            ],
            [
                'day' => [$dayOfWeekRule],
                'date_time' => [$dayTimeRule],
            ]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass when a valid dayOfWeek and a valid time range are provided.');
    }

    /** @test that an invalid dayOfWeek and time range combination fails validation. */
    public function testInvalidDayOfWeekAndTimeFails()
    {
        $dayOfWeekRule = new EnumRule(DayOfWeek::class);
        $dayTimeRule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '09:01', 'America/New_York');

        $validator = Validator::make(
            [
                'day' => 'Atlantis',
                'date_time' => '2024-10-14 18:00:00 UTC',
            ],
            [
                'day' => [$dayOfWeekRule],
                'date_time' => [$dayTimeRule],
            ]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail when an invalid dayOfWeek and an out-of-range time are provided.');
        $this->assertStringContainsString('The day is not a valid instance of', $validator->errors()->first('day'));
        $this->assertStringContainsString('The date_time is not within the allowed time window', $validator->errors()->first('date_time'));
    }
}

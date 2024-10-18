<?php

namespace Tests\Feature\Rules;

use Illuminate\Support\Facades\Validator;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;

/** Test {@link DayTimeRule}. */
class DayTimeRuleTest extends TestCase
{

    /** @test the rule used in a validator. */
    public function validatorPass()
    {
        $rule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'America/New_York');

        $validator = Validator::make(
            ['dateTime' => '2024-10-14 13:00:00 UTC'],
            ['dateTime' => [$rule]]
        );

        /* Should pass, as it's 09:00 in New York (13:00 UTC) */
        $this->assertTrue($validator->passes());
        $this->assertEmpty($validator->errors()->all());
    }

    /** @test the rule used in a validator. */
    public function validatorFail()
    {
        $rule = new DayTimeRule(DayOfWeek::MONDAY, '09:00', '17:00', 'America/New_York');

        $validator = Validator::make(
            ['dateTime' => '2024-10-14 23:00:00 UTC'],
            ['dateTime' => [$rule]]
        );

        /* Should fail, as it's outside the time range in New York (23:00 UTC is 19:00 in New York) */
        $this->assertFalse($validator->passes());
        $this->assertContains('The dateTime is not within the allowed time window from 09:00 to 17:00.', $validator->errors()->all());
    }

    /** @test the rule used in a validator. */
    public function defaultConstructor()
    {
        $rule = new DayTimeRule();
        $attributes = $rule->toArray();
        $this->assertEquals(DayOfWeek::ALL, $attributes['dayOfWeek']);
        $this->assertEquals('00:00', $attributes['startTime']);
        $this->assertEquals('23:59', $attributes['endTime']);
        $this->assertEquals('UTC', $attributes['timezone']);
    }
}

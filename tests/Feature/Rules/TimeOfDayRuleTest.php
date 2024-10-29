<?php

namespace Tests\Feature\Rules;

use Illuminate\Support\Facades\Validator;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Rules\TimeOfDayRule;

class TimeOfDayRuleTest extends TestCase
{
    /**
     * Test TimeOfDayRule with a valid time.
     */
    public function testValidTime()
    {
        $data = ['time' => '08:30'];

        $rule = ['time' => [new TimeOfDayRule]];

        $validator = Validator::make($data, $rule);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test TimeOfDayRule with an invalid time format.
     */
    public function testInvalidTimeFormat()
    {
        $data = ['time' => 'invalid-time'];

        $rule = ['time' => [new TimeOfDayRule]];

        $validator = Validator::make($data, $rule);

        $this->assertFalse($validator->passes());
        $this->assertEquals('The time is not in a valid time format.', $validator->errors()->first('time'));
    }

    /**
     * Test TimeOfDayRule with a null value.
     */
    public function testNullValue()
    {
        $data = ['time' => null];

        $rule = ['time' => [new TimeOfDayRule]];

        $validator = Validator::make($data, $rule);

        $this->assertTrue($validator->passes());
    }
}

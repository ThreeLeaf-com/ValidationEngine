<?php

namespace Tests\Feature\Rules;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Rules\TimeOfDayRule;
use ThreeLeaf\ValidationEngine\Rules\TimesOfDayRule;

class TimesOfDayRuleTest extends TestCase
{
    private TimesOfDayRule $timesOfDayRule;

    /**
     * @test
     * Test that validation passes for a time within any allowed range.
     */
    public function testValidationPassesForValidTime()
    {
        $validator = Validator::make(
            ['time' => '10:00'],
            ['time' => $this->timesOfDayRule]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass for a valid time within the range.');
    }

    /**
     * @test
     * Test that validation passes for a time in a second valid range.
     */
    public function testValidationPassesForTimeInSecondRange()
    {
        $validator = Validator::make(
            ['time' => '15:00'],
            ['time' => $this->timesOfDayRule]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass for a time within the second range.');
    }

    /**
     * @test
     * Test that validation fails for a time outside all allowed ranges.
     */
    public function testValidationFailsForInvalidTime()
    {
        $validator = Validator::make(
            ['time' => '20:00'],
            ['time' => [$this->timesOfDayRule]]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail for a time outside all ranges.');
        $this->assertSame(
            'The time is not within any of the allowed time ranges.',
            $validator->errors()->first('time')
        );
    }

    /**
     * @test
     * Test that validation fails for an invalid date format.
     */
    public function testValidationFailsForInvalidDateFormat()
    {
        $validator = Validator::make(
            ['time' => 'invalid-time'],
            ['time' => [$this->timesOfDayRule]]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail for an invalid date format.');
        $this->assertSame(
            'The time is not within any of the allowed time ranges.',
            $validator->errors()->first('time')
        );
    }

    /**
     * @test
     * Test that validation passes with a null value using the current time.
     */
    public function testValidationPassesForNullValue()
    {
        Carbon::setTestNow(Carbon::create('2023-10-29 09:00', 'UTC'));

        $validator = Validator::make(
            ['time' => null],
            ['time' => $this->timesOfDayRule]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass for null value using the current time.');
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Create multiple TimeOfDayRule instances
        $timeRule1 = new TimeOfDayRule('08:00', '12:00', 'UTC');
        $timeRule2 = new TimeOfDayRule('14:00', '18:00', 'UTC');
        $this->timesOfDayRule = new TimesOfDayRule([$timeRule1, $timeRule2]);
    }
}

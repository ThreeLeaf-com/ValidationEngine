<?php

namespace Tests\Feature\Rules;

use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use ReflectionException;
use ReflectionMethod;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link EnumRule}. */
class EnumRuleTest extends TestCase
{
    /** Test {@link EnumRule::validate()}. */
    #[Test]
    public function validate()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::FRIDAY]);

        $validator = Validator::make(
            ['day' => DayOfWeek::FRIDAY],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->passes());
    }

    /** Test {@link EnumRule::validate()} failure. */
    #[Test]
    public function validateFailure()
    {
        $rule = new EnumRule(DayOfWeek::class);

        $validator = Validator::make(
            ['day' => 'InvalidEnum'],
            ['day' => [$rule]]
        );

        $this->assertFalse($validator->passes());
        $this->assertStringContainsString('The day is not a valid instance of', $validator->errors()->first('day'));
    }

    /** Test {@link EnumRule::__construct()} with invalid argument. */
    #[Test]
    public function constructWithNonEnum()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The class Tests\Feature\Rules\InvalidEnum is not a valid enum.');

        new EnumRule(InvalidEnum::class);
    }

    /** Test {@link EnumRule::__construct()} with invalid argument. */
    #[Test]
    public function constructWithIllegalValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one of [NotADayOfWeek] is not a valid instance of ThreeLeaf\ValidationEngine\Enums\DayOfWeek.');

        new EnumRule(DayOfWeek::class, ['NotADayOfWeek']);
    }

    /** Test that a valid enum NAME is passed in. */
    #[Test]
    public function validEnumAbbreviationPasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY]);

        $validator = Validator::make(
            ['day' => 'MONDAY'],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass when a valid enum abbreviation is provided.');
    }

    /** Test that a valid enum value is passed in. */
    #[Test]
    public function validEnumNamePasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::SATURDAY]);

        $validator = Validator::make(
            ['day' => 'Saturday'],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass when a valid enum name is provided.');
    }

    /** Test that an invalid enum value fails. */
    #[Test]
    public function invalidEnumFails()
    {
        $rule = new EnumRule(DayOfWeek::class);

        $validator = Validator::make(
            ['day' => 'Atlantis'],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail when an invalid enum is provided.');
        $this->assertStringContainsString('The day is not a valid instance of', $validator->errors()->first('day'));
    }

    /** Test that a value outside the allowed subset fails. */
    #[Test]
    public function valueOutsideSubsetFails()
    {
        $rule = EnumRule::make([
            'allowedValues' => [DayOfWeek::MONDAY, DayOfWeek::TUESDAY],
            'enumClass' => DayOfWeek::class,
        ]);

        $validator = Validator::make(
            ['day' => 'Wednesday'],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail when the value is outside the allowed subset.');
        $this->assertStringContainsString('The day must be one of the allowed values.', $validator->errors()->first('day'));
    }

    /** Test that a value inside the allowed subset passes. */
    #[Test]
    public function valueInAllowedSubsetPasses()
    {
        $rule = new EnumRule(DayOfWeek::class, [DayOfWeek::MONDAY, DayOfWeek::TUESDAY]);

        $validator = Validator::make(
            ['day' => 'Monday'],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->passes(), 'Validation should pass when the value is within the allowed subset.');
    }

    /** Test that a null value fails. */
    #[Test]
    public function nullValueFails()
    {
        $rule = new EnumRule(DayOfWeek::class);

        $validator = Validator::make(
            ['day' => null],
            ['day' => [$rule]]
        );

        $this->assertTrue($validator->fails(), 'Validation should fail when a null value is provided.');
        $this->assertStringContainsString('The day is not a valid instance of', $validator->errors()->first('day'));
    }

    /**
     * Test {@link EnumRule::convertToEnumByName()} when there is a reflection exception.
     *
     * @throws ReflectionException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    #[Test]
    public function enumRuleReflectionException()
    {
        /* Step 1: Create a valid EnumRule instance with a real enum. */
        $enumRule = new EnumRule(DayOfWeek::class);

        /* Step 2: Use reflection to inject a fake enum class that will force an exception. */
        $enumRule->enumClass = 'Tests\Unit\Rules\FakeEnum';

        /* Step 3: Define a fake class that should cause a failure when treated as an enum. */
        if (!class_exists('Tests\Unit\Rules\FakeEnum')) {
            eval('namespace Tests\Unit\Rules; class FakeEnum {}');
        }

        /* Step 4: Access the protected convertToEnumByName method. */
        $reflectionMethod = new ReflectionMethod(EnumRule::class, 'convertToEnumByName');
        $reflectionMethod->setAccessible(true);

        /* Step 5: Call the method with a value that would normally be processed by reflection. */
        $result = $reflectionMethod->invoke($enumRule, 'MONDAY');

        /* Step 6: Assert that the result is null because the catch block would be triggered. */
        $this->assertNull($result, 'The result should be null when a ReflectionException is caught.');
    }
}

/**
 * A class that is not an enum.
 */
class InvalidEnum
{
    // No parse method
}

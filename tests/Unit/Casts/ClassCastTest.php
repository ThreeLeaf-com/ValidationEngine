<?php

namespace Tests\Unit\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ThreeLeaf\ValidationEngine\Casts\ClassCast;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/**
 * Unit tests for the ClassCast class.
 */
class ClassCastTest extends TestCase
{
    /** Test that ClassCast casts a valid class that implements an interface. */
    #[Test]
    public function castValidClassImplementsInterface()
    {
        // Mock data representing a class implementing ValidationRule
        $classCast = new ClassCast(ValidationRule::class);

        $this->assertInstanceOf(ClassCast::class, $classCast, 'ClassCast should be instantiated for a valid interface implementation.');
    }

    /** Test that ClassCast throws an exception if the class does not exist. */
    #[Test]
    public function castNonExistentClass()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('NonExistentClass is not a valid class, interface, or trait.');

        // Attempt to create a ClassCast with a non-existent class
        new ClassCast('NonExistentClass');
    }

    /** Test that ClassCast throws an exception if the class does not implement or extend the expected type. */
    #[Test]
    public function castClassFailsTypeCheck()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ThreeLeaf\ValidationEngine\Casts\ClassCast does not extend, implement, or use Illuminate\Contracts\Validation\ValidationRule.');

        $classCast = new ClassCast(ValidationRule::class);
        $classCast->set(new Rule(), 'key', ClassCast::class, []);
    }

    /** Test that ClassCast throws an exception if the class doesn't implement the specified interface. */
    #[Test]
    public function castClassFailsWithInvalidInterface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ThreeLeaf\ValidationEngine\Models\Rule does not extend, implement, or use Illuminate\Contracts\Validation\ValidationRule.');

        // Attempt to create a ClassCast with a valid class that does not implement the specified interface
        $classCast = new ClassCast(ValidationRule::class);

        // Test with a class that does not implement the interface
        $classCast->set(new ValidatorRule(), '', Rule::class, []);
    }

    /** Test that ClassCast throws an exception if a class is valid but does not extend or implement the expected type. */
    #[Test]
    public function castValidClassButDoesNotExtend()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not extend');

        // Mock data representing a class that exists but does not extend the required base class
        $classCast = new ClassCast(Rule::class);
        $classCast->set(new ValidatorRule(), '', Validator::class, []);
    }

    /** Test that ClassCast accepts valid class names using traits. */
    #[Test]
    public function castClassWithTrait()
    {
        // Mock a class that uses the trait
        $classCast = new ClassCast(CastsAttributes::class);
        $this->assertInstanceOf(ClassCast::class, $classCast, 'The class uses the required trait and should pass.');
    }
}

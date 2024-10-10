<?php

namespace Tests\Feature\Rules;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Tests\TestCase;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link Rule}. */
class RuleTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Rule::create()}. */
    public function ruleCreate()
    {
        $rule = Rule::create([
            'attribute' => 'status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode(['enum_class' => 'ThreeLeaf\\ValidationEngine\\Enums\\ActiveStatus']),
        ]);

        $this->assertDatabaseHas('v_rules', [
            'rule_id' => $rule->rule_id,
            'attribute' => 'status',
            'rule_type' => EnumRule::class,
        ]);

        $this->assertEquals('status', $rule->attribute);
        $this->assertEquals(EnumRule::class, $rule->rule_type);
        $this->assertJson($rule->parameters);
    }

    /**
     * @test that a rule can be rehydrated
     * @throws BindingResolutionException
     */
    public function ruleRehydration()
    {
        // Create a new Rule with an EnumRule type for ActiveStatus and store it in the database.
        $rule = Rule::create([
            'attribute' => 'active_status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode([
                'enumClass' => 'ThreeLeaf\\ValidationEngine\\Enums\\ActiveStatus',
                'allowedValues' => [ActiveStatus::ACTIVE],
            ]),
        ]);

        // Assume that we have a method to compile validation rules from the database.
        $parameters = json_decode($rule->parameters, true);

        // Use the dynamically retrieved parameters to compile the validation rules.
        $compiledRules = [
            $rule->attribute => [Container::getInstance()->makeWith(EnumRule::class, $parameters)],
        ];

        // Validate with a valid value.
        $data = ['active_status' => ActiveStatus::ACTIVE->value];
        $validator = LaravelValidator::make($data, $compiledRules);

        $this->assertTrue($validator->passes(), 'The validator should pass with a valid enum value.');

        // Validate with an invalid value.
        $invalidData = ['active_status' => 'invalid_status'];
        $validator = LaravelValidator::make($invalidData, $compiledRules);

        $this->assertTrue($validator->fails(), 'The validator should fail with an invalid enum value.');
        $this->assertEquals('The active_status is not a valid instance of ThreeLeaf\ValidationEngine\Enums\ActiveStatus.', $validator->errors()->first('active_status'));
    }

    /** @test {@link Rule::update()}. */
    public function ruleUpdate()
    {
        $rule = Rule::create([
            'attribute' => 'active_status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode(['enum_class' => 'ThreeLeaf\\ValidationEngine\\Enums\\ActiveStatus']),
        ]);

        // Update the rule's parameters.
        $rule->update([
            'parameters' => json_encode(['enum_class' => 'ThreeLeaf\\ValidationEngine\\Enums\\AnotherStatus']),
        ]);

        $this->assertDatabaseHas('v_rules', [
            'rule_id' => $rule->rule_id,
            'parameters' => json_encode(['enum_class' => 'ThreeLeaf\\ValidationEngine\\Enums\\AnotherStatus']),
        ]);
    }

    /** @test {@link Rule::delete()}. */
    public function ruleDelete()
    {
        $rule = Rule::create([
            'attribute' => 'active_status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode(['enum_class' => 'ThreeLeaf\\ValidationEngine\\Enums\\ActiveStatus']),
        ]);

        $ruleId = $rule->rule_id;
        $rule->delete();

        $this->assertDatabaseMissing('v_rules', [
            'rule_id' => $ruleId,
        ]);
    }
}

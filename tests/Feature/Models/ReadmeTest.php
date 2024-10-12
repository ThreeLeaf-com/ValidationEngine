<?php

namespace Tests\Feature\Models;

use Illuminate\Container\Container;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Tests for README.md examples. */
class ReadmeTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link Rule::create()}. */
    public function ruleCreate()
    {
        /* Create a new validator */
        $newValidator = Validator::create([
            'name' => 'StateAndTimeValidator',
            'description' => 'Validates state and checks time for Monday business hours.',
            'active_status' => ActiveStatus::ACTIVE,
        ]);

        /* Create a rule */
        $newRule = Rule::create([
            'attribute' => 'active_status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode([
                'enumClass' => 'ThreeLeaf\\ValidationEngine\\Enums\\ActiveStatus',
                'allowedValues' => [ActiveStatus::ACTIVE],
            ]),
        ]);

        /* Associate the rule with the validator */
        ValidatorRule::create([
            'validator_id' => $newValidator->validator_id,
            'rule_id' => $newRule->rule_id,
            'order_number' => 1,
            'active_status' => ActiveStatus::INACTIVE,
        ]);

        /* Retrieve the validator */
        $validator = Validator::where('name', 'StateAndTimeValidator')->first();

        /* Extract the rule */
        $rule = $validator->rules->first->get();

        /* Retrieve the rule parameters */
        $parameters = json_decode($rule->parameters, true);
        $compiledRules = [
            $rule->attribute => [Container::getInstance()->makeWith(EnumRule::class, $parameters)],
        ];

        /* Serialize the value you want to validate. */
        $data = ['active_status' => ActiveStatus::ACTIVE->value];

        /* Create the validator */
        $validator = LaravelValidator::make($data, $compiledRules);

        if ($validator->passes()) {
            Log::info('Success!');
        }

        $this->assertTrue($validator->passes(), 'The validator should pass with a valid enum value.');
    }
}

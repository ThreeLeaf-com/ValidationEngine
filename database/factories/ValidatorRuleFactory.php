<?php

namespace Database\Factories\ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/**
 * Generate random {@link ValidatorRule} data.
 *
 * @mixin ValidatorRule
 */
class ValidatorRuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ValidatorRule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();
        return [
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => $this->faker->numberBetween(1, 10),
            'active_status' => $this->faker->randomElement(ActiveStatus::cases())->value,
        ];
    }
}

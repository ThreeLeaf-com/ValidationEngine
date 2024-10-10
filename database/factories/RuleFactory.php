<?php

namespace Database\Factories\ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\ValidationEngine\Models\Rule;

/**
 * Generate random {@link Rule} data.
 *
 * @mixin Rule
 */
class RuleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rule::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'rule_id' => $this->faker->uuid,
            'attribute' => $this->faker->randomElement(['status']),
            'rule_type' => $this->faker->randomElement(['\ThreeLeaf\ValidationEngine\Rules\EnumRule']),
            'parameters' => json_encode([
                'enum_class' => '\ThreeLeaf\ValidationEngine\Enums\ActiveStatus',
                'allowed_values' => ['Active'],
            ]),
        ];
    }
}

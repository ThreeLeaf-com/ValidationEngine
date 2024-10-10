<?php

namespace Database\Factories\ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use ThreeLeaf\ValidationEngine\Models\Validator;

/**
 * Generate random {@link Validator} data.
 *
 * @mixin Validator
 */
class ValidatorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Validator::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'validator_id' => $this->faker->uuid,
            'name' => $this->faker->unique()->word,
            'description' => $this->faker->sentence,
        ];
    }
}

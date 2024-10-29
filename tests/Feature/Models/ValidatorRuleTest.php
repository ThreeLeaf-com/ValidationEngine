<?php

namespace Tests\Feature\Models;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/** Test {@link ValidatorRule}. */
class ValidatorRuleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Test that the validator_rules table exists with the required columns.
     */
    public function test_validator_rules_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasTable(ValidatorRule::TABLE_NAME));
        $columns = ['validator_id', 'rule_id', 'order_number', 'created_at', 'updated_at'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn(ValidatorRule::TABLE_NAME, $column));
        }
    }

    /**
     * @test
     * Test creating a ValidatorRule model using the compound key.
     */
    public function test_create_validator_rule()
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();

        $validatorRule = ValidatorRule::factory()->create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 1,
        ]);

        $this->assertDatabaseHas(ValidatorRule::TABLE_NAME, [
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 1,
        ]);
    }

    /**
     * @test
     * Test updating a ValidatorRule model with the compound key.
     */
    public function test_update_validator_rule()
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();

        $validatorRule = ValidatorRule::factory()->create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 1,
        ]);

        $validatorRule->update(['order_number' => 2]);

        $this->assertDatabaseHas(ValidatorRule::TABLE_NAME, [
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 2,
        ]);
    }

    /**
     * @test
     * Test deleting a ValidatorRule model using the compound key.
     */
    public function test_delete_validator_rule()
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();

        $validatorRule = ValidatorRule::factory()->create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
        ]);

        $validatorRule->delete();

        $this->assertDatabaseMissing(ValidatorRule::TABLE_NAME, [
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
        ]);
    }

    /**
     * @test
     * Test the relationship between ValidatorRule and Validator.
     */
    public function test_validator_relationship()
    {
        $validator = Validator::factory()->create();
        $validatorRule = ValidatorRule::factory()->create(['validator_id' => $validator->validator_id]);

        $this->assertTrue($validatorRule->validator->is($validator));
    }

    /**
     * @test
     * Test the relationship between ValidatorRule and Rule.
     */
    public function test_rule_relationship()
    {
        $rule = Rule::factory()->create();
        $validatorRule = ValidatorRule::factory()->create(['rule_id' => $rule->rule_id]);

        $this->assertTrue($validatorRule->rule->is($rule));
    }

    /**
     * @test
     * Test unique constraint on compound key of validator_id and rule_id.
     */
    public function test_compound_key_uniqueness()
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();

        ValidatorRule::factory()->create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
        ]);

        $this->expectException(QueryException::class);

        ValidatorRule::factory()->create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
        ]);
    }
}

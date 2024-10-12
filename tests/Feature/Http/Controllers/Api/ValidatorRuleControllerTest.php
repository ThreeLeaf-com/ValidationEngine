<?php

namespace Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorRuleController;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/** Test {@link ValidatorRuleController}. */
class ValidatorRuleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link ValidatorRuleController::index()}. */
    public function index()
    {
        ValidatorRule::factory()->count(3)->create();

        $response = $this->getJson('/api/validator-rules');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /** @test {@link ValidatorRuleController::store()}. */
    public function store()
    {
        $validator = Validator::factory()->create();
        $rule = Rule::factory()->create();
        $data = [
            Validator::PRIMARY_KEY => $validator->validator_id,
            Rule::PRIMARY_KEY => $rule->rule_id,
            'order_number' => 1,
            'active_status' => ActiveStatus::INACTIVE,
        ];

        $this->assertDatabaseMissing(ValidatorRule::TABLE_NAME, [Validator::PRIMARY_KEY => $validator->validator_id]);

        $response = $this->postJson('/api/validator-rules', $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas(ValidatorRule::TABLE_NAME, [Validator::PRIMARY_KEY => $validator->validator_id]);
    }

    /** @test {@link ValidatorRuleController::store()} with invalid Validator or Rule UUID. */
    public function storeIntegrityConstraint()
    {
        $data = [
            Validator::PRIMARY_KEY => 'some-uuid',
            Rule::PRIMARY_KEY => 'some-rule-id',
            'order_number' => 1,
            'active_status' => ActiveStatus::INACTIVE,
        ];

        $response = $this->postJson('/api/validator-rules', $data);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test {@link ValidatorRuleController::show()}. */
    public function show()
    {
        $validatorRule = ValidatorRule::factory()->create();

        $response = $this->getJson("/api/validator-rules/$validatorRule->validator_rule_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment([ValidatorRule::PRIMARY_KEY => $validatorRule->validator_rule_id]);
    }

    /** @test {@link ValidatorRuleController::update()}. */
    public function update()
    {
        $validatorRule = ValidatorRule::factory()->create(['order_number' => 1]);

        $updatedData = [
            'order_number' => 2,
            'active_status' => ActiveStatus::ACTIVE,
        ];

        $this->assertDatabaseMissing(ValidatorRule::TABLE_NAME, ['order_number' => 2]);

        $response = $this->putJson("/api/validator-rules/$validatorRule->validator_rule_id", $updatedData);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment($updatedData);
        $this->assertDatabaseHas(ValidatorRule::TABLE_NAME, ['order_number' => 2]);
    }

    /** @test {@link ValidatorRuleController::destroy()}. */
    public function destroy()
    {
        $validatorRule = ValidatorRule::factory()->create();

        $this->assertDatabaseHas(ValidatorRule::TABLE_NAME, [ValidatorRule::PRIMARY_KEY => $validatorRule->validator_rule_id]);

        $response = $this->deleteJson("/api/validator-rules/$validatorRule->validator_rule_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(ValidatorRule::TABLE_NAME, [ValidatorRule::PRIMARY_KEY => $validatorRule->validator_rule_id]);
    }
}

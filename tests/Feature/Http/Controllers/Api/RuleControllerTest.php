<?php

namespace Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\RuleController;
use ThreeLeaf\ValidationEngine\Models\Rule;

/** Test {@link RuleController}. */
class RuleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test {@link RuleController::index()}. */
    public function index()
    {
        Rule::factory()->count(3)->create();

        $response = $this->getJson('/api/rules');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /** @test {@link RuleController::store()}. */
    public function store()
    {
        $data = [
            'attribute' => 'status',
            'rule_type' => 'EnumRule',
            'parameters' => json_encode(['enum_class' => 'App\\Enums\\StatusEnum']),
        ];

        $response = $this->postJson('/api/rules', $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
        $response->assertJsonFragment(['attribute' => 'status']);
        $this->assertDatabaseHas(Rule::TABLE_NAME, ['attribute' => 'status']);
    }

    /** @test {@link RuleController::show()}. */
    public function show()
    {
        $rule = Rule::factory()->create();

        $response = $this->getJson("/api/rules/$rule->rule_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['attribute' => $rule->attribute]);
    }

    /** @test {@link RuleController::update()}. */
    public function update()
    {
        $rule = Rule::factory()->create();

        $updatedData = [
            'attribute' => 'updated_attribute',
            'rule_type' => 'EnumRule',
            'parameters' => json_encode(['enum_class' => 'App\\Enums\\UpdatedEnum']),
        ];

        $response = $this->putJson("/api/rules/$rule->rule_id", $updatedData);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['attribute' => 'updated_attribute']);
        $this->assertDatabaseHas(Rule::TABLE_NAME, ['attribute' => 'updated_attribute']);
    }

    /** @test {@link RuleController::destroy()}. */
    public function destroy()
    {
        $rule = Rule::factory()->create();

        $response = $this->deleteJson("/api/rules/$rule->rule_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Rule::TABLE_NAME, [Rule::PRIMARY_KEY => $rule->rule_id]);
    }
}

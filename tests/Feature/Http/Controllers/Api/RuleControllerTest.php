<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\RuleController;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Rules\EnumRule;

/** Test {@link RuleController}. */
class RuleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** Test {@link RuleController::index()}. */
    #[Test]
    public function index()
    {
        Rule::factory()->count(3)->create();

        $response = $this->getJson('/api/rules');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /** Test {@link RuleController::store()}. */
    #[Test]
    public function store()
    {
        $data = [
            'attribute' => 'status',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode(['enum_class' => 'App\\Enums\\StatusEnum']),
        ];

        $response = $this->postJson('/api/rules', $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
        $response->assertJsonFragment(['attribute' => 'status']);
        $this->assertDatabaseHas(Rule::TABLE_NAME, ['attribute' => 'status']);
    }

    /** Test {@link RuleController::show()}. */
    #[Test]
    public function show()
    {
        $rule = Rule::factory()->create();

        $response = $this->getJson("/api/rules/$rule->rule_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['attribute' => $rule->attribute]);
    }

    /** Test {@link RuleController::update()}. */
    #[Test]
    public function update()
    {
        $rule = Rule::factory()->create();

        $updatedData = [
            'attribute' => 'updated_attribute',
            'rule_type' => EnumRule::class,
            'parameters' => json_encode(['enum_class' => 'App\\Enums\\UpdatedEnum']),
        ];

        $response = $this->putJson("/api/rules/$rule->rule_id", $updatedData);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['attribute' => 'updated_attribute']);
        $this->assertDatabaseHas(Rule::TABLE_NAME, ['attribute' => 'updated_attribute']);
    }

    /** Test {@link RuleController::destroy()}. */
    #[Test]
    public function destroy()
    {
        $rule = Rule::factory()->create();

        $response = $this->deleteJson("/api/rules/$rule->rule_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Rule::TABLE_NAME, [Rule::PRIMARY_KEY => $rule->rule_id]);
    }
}

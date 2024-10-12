<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\TestCase;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorController;
use ThreeLeaf\ValidationEngine\Models\Validator;

/** Test {@link ValidatorController}. */
class ValidatorControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** @test {@link ValidatorController::index()}. */
    public function index()
    {
        Validator::factory()->count(3)->create();

        $response = $this->getJson('/api/validators');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /** @test {@link ValidatorController::store()}. */
    public function store()
    {
        $data = [
            'name' => 'Test Validator',
            'description' => 'A test validator',
        ];

        $response = $this->postJson('/api/validators', $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas(Validator::TABLE_NAME, ['name' => 'Test Validator']);
    }

    /** @test {@link ValidatorController::show()}. */
    public function show()
    {
        $validator = Validator::factory()->create();

        $response = $this->getJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['name' => $validator->name]);
    }

    /** @test {@link ValidatorController::update()}. */
    public function update()
    {
        $validator = Validator::factory()->create();
        $newName = $this->faker()->name();

        $updatedData = [
            'name' => $newName,
            'description' => 'An updated description',
        ];

        $this->assertDatabaseHas(Validator::TABLE_NAME, ['name' => $validator->name]);
        $this->assertDatabaseMissing(Validator::TABLE_NAME, ['name' => $newName]);

        $response = $this->putJson("/api/validators/$validator->validator_id", $updatedData);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment($updatedData);
        $this->assertDatabaseHas(Validator::TABLE_NAME, ['name' => $newName]);
        $this->assertDatabaseMissing(Validator::TABLE_NAME, ['name' => $validator->name]);
    }

    /** @test {@link ValidatorController::destroy()}. */
    public function destroy()
    {
        $validator = Validator::factory()->create();

        $response = $this->deleteJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Validator::TABLE_NAME, [Validator::PRIMARY_KEY => $validator->validator_id]);
    }
}

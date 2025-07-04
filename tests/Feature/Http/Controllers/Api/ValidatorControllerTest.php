<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorController;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;
use ThreeLeaf\ValidationEngine\Services\ValidatorService;

/** Test {@link ValidatorController}. */
class ValidatorControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /** Test {@link ValidatorController::index()}. */
    #[Test]
    public function index()
    {
        Validator::factory()->count(3)->create();

        $response = $this->getJson('/api/validators');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /** Test {@link ValidatorController::store()}. */
    #[Test]
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

    /** Test {@link ValidatorController::show()}. */
    #[Test]
    public function show()
    {
        $validator = Validator::factory()->create();

        $response = $this->getJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['name' => $validator->name]);
    }

    /** Test {@link ValidatorController::update()}. */
    #[Test]
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

    /** Test {@link ValidatorController::destroy()}. */
    #[Test]
    public function destroy()
    {
        $validator = Validator::factory()->create();

        $response = $this->deleteJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(Validator::TABLE_NAME, [Validator::PRIMARY_KEY => $validator->validator_id]);
    }

    /**
     * Test
     * Positive test for {@link ValidatorController::doValidation()}.
     * Test that the validation passes for valid data and validator.
     */
    #[Test]
    public function validatePasses()
    {
        $validator = Validator::factory()->create(['name' => 'StateValidator', 'active_status' => ActiveStatus::ACTIVE]);
        $rule = Rule::factory()->create();
        ValidatorRule::factory()->create([
            Validator::PRIMARY_KEY => $validator->validator_id,
            Rule::PRIMARY_KEY => $rule->rule_id,
            'order_number' => 1,
        ]);

        $data = ['key' => 'valid_value'];

        /* Mocking the runValidator method to return true (validation passes) */
        $this->partialMock(ValidatorService::class, function ($mock) use ($validator, $data) {
            $mock->shouldReceive('runValidator')
                ->andReturn(true);
        });

        $response = $this->postJson('/api/validators/validate', ['validator_id' => $validator->validator_id] + $data);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJson(['success' => true]);
    }

    /**
     * Test
     * Negative test for {@link ValidatorController::doValidation()}.
     * Test that the validation fails for invalid data.
     */
    #[Test]
    public function validateFails()
    {
        $validator = Validator::factory()->create(['name' => 'StateValidator', 'active_status' => ActiveStatus::ACTIVE]);
        $rule = Rule::factory()->create();
        ValidatorRule::factory()->create([
            Validator::PRIMARY_KEY => $validator->validator_id,
            Rule::PRIMARY_KEY => $rule->rule_id,
            'order_number' => 1,
        ]);

        $data = ['key' => 'invalid_value'];

        /* Mocking the runValidator method to return false (validation fails) */
        $this->partialMock(ValidatorService::class, function ($mock) use ($validator, $data) {
            $mock->shouldReceive('runValidator')
                ->andReturn(false);
            $mock->shouldReceive('getErrors')
                ->andReturn(['key' => ['Validation error']]);
        });

        $response = $this->postJson('/api/validators/validate', ['validator_id' => $validator->validator_id] + $data);

        $response->assertStatus(HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson(['success' => false]);
    }
}

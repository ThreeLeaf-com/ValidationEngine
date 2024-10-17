<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Models\Validator;

class ValidatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index functionality for retrieving validators.
     *
     * @return void
     */
    public function test_index_validators()
    {
        // Create some validator records
        Validator::factory()->count(3)->create();

        // Test index functionality
        $response = $this->getJson('/api/validators');

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonCount(3);
    }

    /**
     * Test store functionality for creating a new validator.
     *
     * @return void
     */
    public function test_store_validator()
    {
        $data = [
            'name' => 'Test Validator',
            'description' => 'Validator description',
        ];

        $response = $this->postJson('/api/validators', $data);

        $response->assertStatus(HttpCodes::HTTP_CREATED);
        $response->assertJsonFragment($data);
        $this->assertDatabaseHas(VAlidator::TABLE_NAME, $data);
    }

    /**
     * Test show functionality for retrieving a single validator.
     *
     * @return void
     */
    public function test_show_validator()
    {
        $validator = Validator::factory()->create();

        $response = $this->getJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment(['name' => $validator->name]);
    }

    /**
     * Test update functionality for updating a validator.
     *
     * @return void
     */
    public function test_update_validator()
    {
        $validator = Validator::factory()->create();

        $updatedData = [
            'name' => 'Updated Validator Name',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/validators/$validator->validator_id", $updatedData);

        $response->assertStatus(HttpCodes::HTTP_OK);
        $response->assertJsonFragment($updatedData);
        $this->assertDatabaseHas(VAlidator::TABLE_NAME, $updatedData);
    }

    /**
     * Test destroy functionality for deleting a validator.
     *
     * @return void
     */
    public function test_destroy_validator()
    {
        $validator = Validator::factory()->create();

        $response = $this->deleteJson("/api/validators/$validator->validator_id");

        $response->assertStatus(HttpCodes::HTTP_NO_CONTENT);
        $this->assertDatabaseMissing(VAlidator::TABLE_NAME, ['validator_id' => $validator->validator_id]);
    }
}

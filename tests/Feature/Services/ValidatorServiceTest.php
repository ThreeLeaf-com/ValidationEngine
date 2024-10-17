<?php

namespace Tests\Feature\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;
use ThreeLeaf\ValidationEngine\Services\RuleService;
use ThreeLeaf\ValidationEngine\Services\ValidatorService;

/** Test {@link ValidatorService}. */
class ValidatorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ValidatorService $validatorService;

    /** @test {@link ValidatorService::runValidator()} with valid validator and valid rules. */
    public function testRunValidatorWithValidData()
    {
        $validator = Validator::create([
            'validator_id' => 'valid-uuid',
            'name' => 'ValidValidator',
            'active_status' => ActiveStatus::ACTIVE,
        ]);

        $rule = Rule::create([
            'rule_id' => 'rule-uuid',
            'attribute' => 'name',
            'rule_type' => DayTimeRule::class,
            'parameters' => json_encode([]),
        ]);

        ValidatorRule::create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 1,
            'active_status' => ActiveStatus::ACTIVE,
        ]);

        // Mock the RuleService to return true for valid rules
        $ruleServiceMock = Mockery::mock(RuleService::class);
        $ruleServiceMock->shouldReceive('validateRules')
            ->once()
            ->andReturn(true);

        $this->app->instance(RuleService::class, $ruleServiceMock);

        $this->validatorService = $this->app->make(ValidatorService::class);

        $isValid = $this->validatorService->runValidatorById($validator->validator_id, ['name' => 'valid']);
        $this->assertTrue($isValid, 'The data should be valid for the given validator and rule set.');
    }

    /** @test {@link ValidatorService::runValidator()} with invalid data. */
    public function testRunValidatorWithInvalidData()
    {
        $validator = Validator::create([
            'validator_id' => 'invalid-uuid',
            'name' => 'InvalidValidator',
            'active_status' => ActiveStatus::ACTIVE,
        ]);

        $rule = Rule::create([
            'rule_id' => 'rule-uuid',
            'attribute' => 'name',
            'rule_type' => DayTimeRule::class,
            'parameters' => json_encode([]),
        ]);

        ValidatorRule::create([
            'validator_id' => $validator->validator_id,
            'rule_id' => $rule->rule_id,
            'order_number' => 1,
            'active_status' => ActiveStatus::ACTIVE,
        ]);

        // Mock the RuleService to return false for invalid rules
        $ruleServiceMock = Mockery::mock(RuleService::class);
        $ruleServiceMock->shouldReceive('validateRules')
            ->once()
            ->with(Mockery::type('array'), ['name' => null])
            ->andReturn(false);

        $this->app->instance(RuleService::class, $ruleServiceMock);

        $this->validatorService = $this->app->make(ValidatorService::class);

        $isValid = $this->validatorService->runValidatorById($validator->validator_id, ['name' => null]);
        $this->assertFalse($isValid, 'The data should be invalid for the given validator and rule set.');
    }

    /** @test {@link ValidatorService::runValidator()} with non-existent validator. */
    public function testRunValidatorWithNonExistentValidator()
    {
        $ruleServiceMock = Mockery::mock(RuleService::class);
        $ruleServiceMock->shouldNotReceive('validateRules');

        $this->app->instance(RuleService::class, $ruleServiceMock);

        $this->validatorService = $this->app->make(ValidatorService::class);

        // Non-existent validator ID
        $isValid = $this->validatorService->runValidatorById('non-existent-uuid', ['name' => 'value']);
        $this->assertFalse($isValid, 'The validation should fail with a non-existent validator.');
    }

    /** @test {@link ValidatorService::runValidator()} with inactive validator. */
    public function testRunValidatorWithInactiveValidator()
    {
        $validator = Validator::create([
            'validator_id' => 'inactive-uuid',
            'name' => 'InactiveValidator',
            'active_status' => ActiveStatus::INACTIVE,
        ]);

        // Mock the RuleService, but it shouldn't be called since the validator is inactive
        $ruleServiceMock = Mockery::mock(RuleService::class);
        $ruleServiceMock->shouldNotReceive('validateRules');

        $this->app->instance(RuleService::class, $ruleServiceMock);

        $this->validatorService = $this->app->make(ValidatorService::class);

        $isValid = $this->validatorService->runValidatorById($validator->validator_id, ['name' => 'value']);
        $this->assertFalse($isValid, 'The validation should fail because the validator is inactive.');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->validatorService = app(ValidatorService::class);
    }
}

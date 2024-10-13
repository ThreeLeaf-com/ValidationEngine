<?php

namespace Tests\Feature\Services;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Tests\Feature\TestCase;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Rules\DayTimeRule;
use ThreeLeaf\ValidationEngine\Services\RuleService;

/** Test {@link RuleService}. */
class RuleServiceTest extends TestCase
{
    protected RuleService $ruleService;

    /** @test {@link RuleService::validateRules()} with valid rule. */
    public function validateRules()
    {
        $rule = new Rule();
        $rule->rule_type = DayTimeRule::class;
        $rule->parameters = json_encode([]);

        LaravelValidator::shouldReceive('make')->andReturnSelf();
        LaravelValidator::shouldReceive('passes')->andReturn(true);

        $isValid = $this->ruleService->validateRules([$rule], ['key' => 'value']);

        $this->assertTrue($isValid, 'The data should be valid with valid rules.');
    }

    /** @test {@link RuleService::validateRules()} with invalid rule. */
    public function validateRulesInvalidRule()
    {
        $rule = new Rule();
        $rule->rule_type = DayTimeRule::class;
        $rule->parameters = json_encode([]);

        // Mock Laravel Validator to fail
        LaravelValidator::shouldReceive('make')->andReturnSelf();
        LaravelValidator::shouldReceive('passes')->andReturn(false);

        $isValid = $this->ruleService->validateRules([$rule], ['key' => 'invalid_value']);

        $this->assertFalse($isValid, 'The data should be invalid with invalid rules.');
    }

    /** @test {@link RuleService::compileRule()} compiles valid rule. */
    public function compileRule()
    {
        $rule = new Rule();
        $rule->rule_type = DayTimeRule::class;
        $rule->parameters = json_encode(['param' => 'value']);

        $ruleInstanceMock = $this->mock(ValidationRule::class);

        /* Bind the rule type class to the container */
        $this->app->bind($rule->rule_type, function () use ($ruleInstanceMock) {
            return $ruleInstanceMock;
        });

        $compiledRule = $this->ruleService->compileRule($rule);

        $this->assertInstanceOf(ValidationRule::class, $compiledRule, 'The rule should be compiled successfully.');
    }

    /** @test {@link RuleService::compileRule()} returns null when compilation fails. */
    public function compileRuleInvalidRule()
    {
        $rule = new Rule();
        $rule->setRawAttributes(['rule_type' => 'InvalidClass']);
        $rule->parameters = json_encode(['param' => 'invalid_value']);

        Log::shouldReceive('error')->once();

        $compiledRule = $this->ruleService->compileRule($rule);

        $this->assertNull($compiledRule, 'The rule should return null if an exception occurs.');
    }

    /** @test {@link RuleService::validateRules()} that processes valid rule and ignores invalid rule. */
    public function validateRulesValidAndInvalidRules()
    {
        $rule = new Rule();
        $rule->rule_type = DayTimeRule::class;
        $rule->parameters = json_encode([]);

        $ruleInvalid = new Rule();
        $ruleInvalid->setRawAttributes(['rule_type' => 'InvalidClass']);
        $ruleInvalid->parameters = json_encode([]);

        $ruleInstanceMock = $this->mock(ValidationRule::class);

        $this->app->bind($rule->rule_type, function () use ($ruleInstanceMock) {
            return $ruleInstanceMock;
        });

        LaravelValidator::shouldReceive('make')->andReturnSelf();
        LaravelValidator::shouldReceive('passes')->andReturn(true);

        Log::shouldReceive('error')->once();

        $isValid = $this->ruleService->validateRules([$rule, $ruleInvalid], ['key' => 'value']);

        $this->assertTrue($isValid, 'The data should still pass validation as valid rules are processed.');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->ruleService = app(RuleService::class);
    }
}

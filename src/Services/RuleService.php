<?php

namespace ThreeLeaf\ValidationEngine\Services;

use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;
use ThreeLeaf\ValidationEngine\Models\Rule;
use Throwable;

/** The {@link Rule} service. */
class RuleService
{

    /**
     * Validate the given data against the compiled rules.
     *
     * @param Rule[] $rules The rule set to validate the data against
     * @param array  $data  The data to validate
     *
     * @return boolean True if the data is valid, false otherwise
     */
    public function validateRules(array $rules, array $data): bool
    {
        $passes = true;
        foreach ($rules as $rule) {
            $ruleInstance = $rule->instantiateRule();
            $passes &= $ruleInstance->isValidFor($data[$rule->attribute] ?? null);

            if (!$passes) {
                break;
            }
        }

        return $passes;
    }

    /**
     * Instantiate / deserialize a Laravel {@link ValidationRule} from a {@link Rule} object.
     *
     * @param Rule $rule The rule to instantiate
     *
     * @return ValidationRule|null The instantiated {@link ValidationRule} or null, if the rule cannot be instantiated
     */
    public function compileRule(Rule $rule): ?ValidationRule
    {
        /* Retrieve the rule parameters */
        try {
            return Container::getInstance()->makeWith($rule->rule_type, $rule->parameters);
        } catch (Throwable $exception) {
            $parameters = json_encode($rule->parameters);
            Log::error("Error instantiating '$rule->rule_type' with parameters '$parameters':\n", $exception->getTrace());
            return null;
        }
    }
}

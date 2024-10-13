<?php

namespace ThreeLeaf\ValidationEngine\Services;

use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Validator;

/** The {@link Validator} service. */
class ValidatorService
{
    public function __construct(
        private readonly RuleService $ruleService,
    )
    {
    }

    /**
     * Runs a {@link Validator} against the provided data.
     *
     * @param string $validator_id The {@link Validator::$validator_id} or {@link Validator::name}
     * @param array  $data         The data to validate
     *
     * @return bool true, if the data is valid
     */
    public function runValidator(string $validator_id, array $data): bool
    {
        $validator = Validator::where('active_status', ActiveStatus::ACTIVE)
            ->where(function ($query) use ($validator_id) {
                $query->where('validator_id', $validator_id)
                    ->orWhere('name', $validator_id);
            })
            ->first();

        if (!$validator) {
            return false;
        }

        $rules = $validator->rules()->get();

        return $this->ruleService->validateRules($rules->all(), $data);
    }
}

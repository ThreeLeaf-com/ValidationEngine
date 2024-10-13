<?php

namespace ThreeLeaf\ValidationEngine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule as ValidationRule;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;
use ThreeLeaf\ValidationEngine\Models\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/**
 * Validates a {@link ValidatorRule} request.
 *
 * @OA\Schema(
 *     schema="ValidatorRuleRequest",
 *     required={"validator_id", "rule_id", "order_number", "active_status"},
 *     @OA\Property(property="validator_id", type="string", format="uuid", example="3fa85f64-5717-4562-b3fc-2c963f66afa6", description="The ID of the validator"),
 *     @OA\Property(property="rule_id", type="string", format="uuid", example="6fa85f64-5717-4562-b3fc-2c963f66afg7", description="The ID of the rule"),
 *     @OA\Property(property="order_number", type="integer", example=1, description="The order number of the rule in the validator"),
 *     @OA\Property(property="active_status", type="string", example="Active", description="The status of the rule, either 'Active' or 'Inactive'")
 * )
 *
 * @mixin ValidatorRule
 */
class ValidatorRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'order_number' => [
                'required',
                'integer',
                'min:1',
                ValidationRule::unique(ValidatorRule::TABLE_NAME)
                    ->where('validator_id', $this->validator_id)
                    ->ignore($this->rule_id, 'rule_id'),
            ],
            'active_status' => [
                'required',
                'string',
                ValidationRule::enum(ActiveStatus::class),
            ],
        ];

        if ($this->isMethod('POST')) {
            $rules['validator_id'] = [
                'required',
                'uuid',
                ValidationRule::exists(Validator::TABLE_NAME, Validator::PRIMARY_KEY),
            ];
            $rules['rule_id'] = [
                'required',
                'uuid',
                ValidationRule::exists(Rule::TABLE_NAME, Rule::PRIMARY_KEY),
                ValidationRule::unique(ValidatorRule::TABLE_NAME)
                    ->where('validator_id', $this->validator_id),
            ];
        } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['validator_id'] = [
                'required',
                'uuid',
                ValidationRule::exists(Validator::TABLE_NAME, Validator::PRIMARY_KEY),
                ValidationRule::in([$this->route('validator_id')]),
            ];
            $rules['rule_id'] = [
                'required',
                'uuid',
                ValidationRule::exists(Rule::TABLE_NAME, Rule::PRIMARY_KEY),
                ValidationRule::in([$this->route('rule_id')]),
            ];
        }

        return $rules;
    }

    /**
     * Returns custom validation messages for the request.
     *
     * @return array<string, string> An array of custom validation messages.
     */
    public function messages(): array
    {
        return [
            'rule_id.unique' => 'This rule is already associated with this validator.',
            'order_number.unique' => 'This order number is already in use for this validator.',
            'validator_id.in' => 'The validator ID cannot be changed.',
            'rule_id.in' => 'The rule ID cannot be changed.',
            'active_status.enum' => 'The active status must be either Active or Inactive.',
        ];
    }
}

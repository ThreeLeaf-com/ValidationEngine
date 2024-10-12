<?php

namespace ThreeLeaf\ValidationEngine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use ThreeLeaf\ValidationEngine\Models\Rule;

/**
 * Validates a {@link Rule} request.
 *
 * @OA\Schema(
 *     schema="RuleRequest",
 *     required={"attribute", "rule_type"},
 *     @OA\Property(property="attribute", type="string", description="The attribute being validated"),
 *     @OA\Property(property="rule_type", type="string", description="The type of the validation rule"),
 *     @OA\Property(property="parameters", type="string", description="JSON-encoded parameters specific to the rule type"),
 * )
 *
 * @mixin Rule
 */
class RuleRequest extends FormRequest
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
     * @return array<string, \Illuminate\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'attribute' => 'required|string|max:255',
            'rule_type' => 'required|string|max:255',
            'parameters' => 'nullable|string',
        ];
    }
}

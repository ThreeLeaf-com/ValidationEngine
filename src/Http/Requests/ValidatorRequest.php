<?php

namespace ThreeLeaf\ValidationEngine\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use ThreeLeaf\ValidationEngine\Models\Validator;

/**
 * Validates a {@link Validator} request.
 *
 * @OA\Schema(
 *     schema="ValidatorRequest",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", example="My Validator", description="The name of the validator"),
 *     @OA\Property(property="description", type="string", example="A description of the validator", description="A brief description of the validator")
 * )
 *
 * @mixin Validator
 */
class ValidatorRequest extends FormRequest
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
     * @return array<string, Rule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Validator::TABLE_NAME),
            ],
            'description' => 'nullable|string|max:1000',
        ];

        /* If this is an update request (PUT or PATCH), ignore the uniqueness rules for this ID only,
         * as this object may or may not have the same name as it did before. */
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $validatorId = $this->route('validator');
            $rules['name'][3] = Rule::unique(Validator::TABLE_NAME)->ignore($validatorId);
        }

        return $rules;
    }
}

<?php

namespace ThreeLeaf\ValidationEngine\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\ValidationEngine\Http\Controllers\Controller;
use ThreeLeaf\ValidationEngine\Http\Requests\ValidatorRuleRequest;
use ThreeLeaf\ValidationEngine\Models\ValidatorRule;

/**
 * Controller for {@link ValidatorRule} REST APIs.
 *
 * Handles CRUD operations for the ValidatorRule model.
 *
 * @OA\Tag(
 *     name="ValidationEngine/Validator Rules",
 *      description="API endpoints for managing validation validator rules"
 * )
 */
class ValidatorRuleController extends Controller
{
    /**
     * Display a listing of the validator rules.
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/validator-rules",
     *     summary="List all validator rules",
     *     tags={"ValidationEngine/Validator Rules"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ValidatorRule"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(ValidatorRule::all());
    }

    /**
     * Store a newly created validator rule in the database.
     *
     * @param ValidatorRuleRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/validator-rules",
     *     summary="Create a new validator rule",
     *     tags={"ValidationEngine/Validator Rules"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Validator rule data",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRuleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Validator rule created",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRule")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function store(ValidatorRuleRequest $request): JsonResponse
    {
        $validatorRule = ValidatorRule::create($request->validated());

        return response()->json($validatorRule, HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified validator rule.
     *
     *
     * @param string $validator_id
     * @param string $rule_id
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/validator-rules/{validator_id}/{rule_id}",
     *     summary="Get a validator rule by ID",
     *     tags={"ValidationEngine/Validator Rules"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="Validator ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="Rule ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRule")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function show(string $validator_id, string $rule_id): JsonResponse
    {
        $validatorRule = ValidatorRule::where('validator_id', $validator_id)
            ->where('rule_id', $rule_id)
            ->firstOrFail();

        return response()->json($validatorRule);
    }

    /**
     * Update the specified validator rule in the database.
     *
     * @param ValidatorRuleRequest $request
     * @param string               $validator_id
     * @param string               $rule_id
     *
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/validator-rules/{validator_id}/{rule_id}",
     *     summary="Update an existing validator rule",
     *     tags={"ValidationEngine/Validator Rules"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="Validator ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="Rule ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Updated ValidatorRule object",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRule")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRule")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function update(ValidatorRuleRequest $request, string $validator_id, string $rule_id): JsonResponse
    {
        $validatorRule = ValidatorRule::where('validator_id', $validator_id)
            ->where('rule_id', $rule_id)
            ->firstOrFail();

        $validatedData = $request->validated();

        $validatorRule->update($validatedData);

        return response()->json($validatorRule);
    }

    /**
     * Remove the specified validator rule from the database.
     *
     * @param string $validator_id
     * @param string $rule_id
     *
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/validator-rules/{validator_id}/{rule_id}",
     *     summary="Delete a validator rule",
     *     tags={"ValidationEngine/Validator Rules"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="Validator ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="Rule ID",
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Success"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function destroy(string $validator_id, string $rule_id): JsonResponse
    {
        $validatorRule = ValidatorRule::where('validator_id', $validator_id)
            ->where('rule_id', $rule_id)
            ->firstOrFail();

        $validatorRule->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}

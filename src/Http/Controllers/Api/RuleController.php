<?php

namespace ThreeLeaf\ValidationEngine\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\ValidationEngine\Http\Controllers\Controller;
use ThreeLeaf\ValidationEngine\Http\Requests\RuleRequest;
use ThreeLeaf\ValidationEngine\Models\Rule;

/**
 * Controller for {@link Rule} REST APIs.
 *
 *  Handles CRUD operations for the Validator model.
 *
 * @OA\Tag(
 *     name="ValidationEngine/Rules",
 *     description="API endpoints for managing validation rules"
 * )
 */
class RuleController extends Controller
{
    /**
     * Display a listing of rules.
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/rules",
     *     summary="List all rules",
     *     tags={"ValidationEngine/Rules"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Rule"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Rule::all());
    }

    /**
     * Store a newly created rule in the database.
     *
     * @param RuleRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/rules",
     *     summary="Create a new rule",
     *     tags={"ValidationEngine/Rules"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Rule data",
     *         @OA\JsonContent(ref="#/components/schemas/RuleRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rule created",
     *         @OA\JsonContent(ref="#/components/schemas/Rule")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function store(RuleRequest $request): JsonResponse
    {
        $rule = Rule::create($request->validated());

        return response()->json($rule, HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified rule.
     *
     * @param Rule $rule
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/rules/{rule_id}",
     *     summary="Get a rule by ID",
     *     tags={"ValidationEngine/Rules"},
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="The rule ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rule")
     *     )
     * )
     */
    public function show(Rule $rule): JsonResponse
    {
        return response()->json($rule);
    }

    /**
     * Update the specified rule in the database.
     *
     * @param RuleRequest $request
     * @param Rule        $rule
     *
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/rules/{rule_id}",
     *     summary="Update an existing rule",
     *     tags={"ValidationEngine/Rules"},
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="The rule ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *          description="Rule data",
     *         @OA\JsonContent(ref="#/components/schemas/RuleRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Rule")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function update(RuleRequest $request, Rule $rule): JsonResponse
    {
        $rule->update($request->validated());

        return response()->json($rule);
    }

    /**
     * Remove the specified rule from the database.
     *
     * @param Rule $rule
     *
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/rules/{rule_id}",
     *     summary="Delete a rule",
     *     tags={"ValidationEngine/Rules"},
     *     @OA\Parameter(
     *         name="rule_id",
     *         in="path",
     *         required=true,
     *         description="The rule ID",
     *         @OA\Schema(type="string")
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
    public function destroy(Rule $rule): JsonResponse
    {
        $rule->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }
}

<?php

namespace ThreeLeaf\ValidationEngine\Http\Controllers\Api;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpCodes;
use ThreeLeaf\ValidationEngine\Http\Controllers\Controller;
use ThreeLeaf\ValidationEngine\Http\Requests\ValidatorRequest;
use ThreeLeaf\ValidationEngine\Models\Validator;
use ThreeLeaf\ValidationEngine\Services\ValidatorService;

/**
 * Controller for {@link Validator} REST APIs.
 *
 * Handles CRUD operations for the Validator model.
 *
 * @OA\Tag(
 *     name="ValidationEngine/Validators",
 *     description="API endpoints for managing validation validators"
 * )
 */
class ValidatorController extends Controller
{
    public function __construct(
        private readonly ValidatorService $validatorService,
    )
    {
    }

    /**
     * Display a listing of the validators.
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/validators",
     *     summary="List all validators",
     *     tags={"ValidationEngine/Validators"},
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Validator"))
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return response()->json(Validator::all());
    }

    /**
     * Store a newly created validator in the database.
     *
     * @param ValidatorRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/api/validators",
     *     summary="Create a new validator",
     *     tags={"ValidationEngine/Validators"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Validator data",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Validator created",
     *         @OA\JsonContent(ref="#/components/schemas/Validator")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     )
     * )
     */
    public function store(ValidatorRequest $request): JsonResponse
    {
        $validator = Validator::create($request->validated());

        return response()->json($validator, HttpCodes::HTTP_CREATED);
    }

    /**
     * Display the specified validator.
     *
     * @param Validator $validator
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/validators/{validator_id}",
     *     summary="Get a validator by ID",
     *     tags={"ValidationEngine/Validators"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="The validator ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Validator")
     *     )
     * )
     */
    public function show(Validator $validator): JsonResponse
    {
        return response()->json($validator);
    }

    /**
     * Update the specified validator in the database.
     *
     * @param ValidatorRequest $request
     * @param Validator        $validator
     *
     * @return JsonResponse
     *
     * @OA\Put(
     *     path="/api/validators/{validator_id}",
     *     summary="Update an existing validator",
     *     tags={"ValidationEngine/Validators"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="The validator ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Validator data",
     *         @OA\JsonContent(ref="#/components/schemas/ValidatorRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(ref="#/components/schemas/Validator")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not found"
     *     )
     * )
     */
    public function update(ValidatorRequest $request, Validator $validator): JsonResponse
    {
        $validator->update($request->validated());

        return response()->json($validator);
    }

    /**
     * Remove the specified validator from the database.
     *
     * @param Validator $validator
     *
     * @return JsonResponse
     *
     * @OA\Delete(
     *     path="/api/validators/{validator_id}",
     *     summary="Delete a validator",
     *     tags={"ValidationEngine/Validators"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="path",
     *         required=true,
     *         description="The validator ID",
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
    public function destroy(Validator $validator): JsonResponse
    {
        $validator->delete();

        return response()->json(null, HttpCodes::HTTP_NO_CONTENT);
    }

    /**
     * Validate data against a specific validator.
     *
     * @OA\Post(
     *     path="/api/validators/validate",
     *     summary="Validate data against a specific validator",
     *     description="Runs validation rules associated with the given validator against the input data.",
     *     tags={"Validator"},
     *     @OA\Parameter(
     *         name="validator_id",
     *         in="query",
     *         description="Validator ID or name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             example={"key": "value"},
     *             description="Data to be validated."
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Validation successful",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"success": true}
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation failed",
     *         @OA\JsonContent(
     *             type="object",
     *             example={"success": false, "errors": {"field": {"Validation error"}}}
     *         )
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function doValidation(Request $request): JsonResponse
    {
        $validator_id = $request->input('validator_id');
        $data = $request->all();

        try {
            $isValid = $this->validatorService->runValidator($validator_id, $data);
            if ($isValid) {
                return response()->json(['success' => true], HttpCodes::HTTP_OK);
            } else {
                return response()->json(['success' => false], HttpCodes::HTTP_UNPROCESSABLE_ENTITY);
            }
        } catch (Exception $exception) {
            Log::error("Validation failed: {$exception->getMessage()}");
            return response()->json(['success' => false, 'error' => 'Internal Server Error'], HttpCodes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

<?php

use Illuminate\Support\Facades\Route;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\RuleController;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorController;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorRuleController;

Route::apiResource('rules', RuleController::class);

Route::apiResource('validators', ValidatorController::class);
Route::post('/validators/validate', [ValidatorController::class, 'doValidation']);

Route::prefix('validator-rules')->group(function () {
    Route::get('/', [ValidatorRuleController::class, 'index']);
    Route::post('/', [ValidatorRuleController::class, 'store']);
    Route::get('{validator_id}/{rule_id}', [ValidatorRuleController::class, 'show']);
    Route::put('{validator_id}/{rule_id}', [ValidatorRuleController::class, 'update']);
    Route::delete('{validator_id}/{rule_id}', [ValidatorRuleController::class, 'destroy']);
});

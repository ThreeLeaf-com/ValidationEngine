<?php

use Illuminate\Support\Facades\Route;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\RuleController;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorController;
use ThreeLeaf\ValidationEngine\Http\Controllers\Api\ValidatorRuleController;

Route::apiResource('rules', RuleController::class);
Route::apiResource('validators', ValidatorController::class);
Route::apiResource('validator-rules', ValidatorRuleController::class);

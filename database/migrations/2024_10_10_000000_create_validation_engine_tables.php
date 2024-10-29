<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;

/** Create all the Validation Engine tables. */
return new class extends Migration {

    /** Run the migrations. */
    public function up(): void
    {
        Schema::create('v_rules', function (Blueprint $table) {
            $table->comment('Stores individual validation rules with their configurations.');
            $table->uuid('rule_id')->primary()->comment('The unique identifier for the Rule.');
            $table->string('attribute')->comment('The attribute being validated, e.g., "state" or "date_time".');
            $table->string('rule_type')->comment('The type of the validation rule, e.g., "EnumRule" or "DayTimeRule".');
            $table->json('parameters')->comment('JSON-encoded parameters specific to the rule type.');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the entry was created.');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the entry was last updated.');
        });

        Schema::create('v_validators', function (Blueprint $table) {
            $table->comment('Stores named validation configurations, combining multiple rules.');
            $table->uuid('validator_id')->primary()->comment('The unique identifier for the Validator.');
            $table->string('name')->unique()->comment('The unique name of the validator, e.g., "StateAndTimeValidator".');
            $table->string('description')->nullable()->comment('A brief description of the validator.');
            $table->string('context')->nullable()->comment('The context of the validator.');
            $table->enum('active_status', [
                ActiveStatus::ACTIVE->value,
                ActiveStatus::INACTIVE->value,
            ])->default(ActiveStatus::ACTIVE->value)->comment('The status of the validator, e.g., "Active" or "Inactive".');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the entry was created.');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the entry was last updated.');
        });

        Schema::create('v_validator_rules', function (Blueprint $table) {
            $table->comment('Associates rules with validators, allowing validators to use multiple rules.');
            $table->uuid('validator_id')->comment('Foreign key referencing the unique ID of the validator.');
            $table->uuid('rule_id')->comment('Foreign key referencing the unique ID of the rule.');
            $table->integer('order_number')->default(1)->comment('The order in which the rule should be applied.');
            $table->enum('active_status', [
                ActiveStatus::ACTIVE->value,
                ActiveStatus::INACTIVE->value,
            ])->default(ActiveStatus::ACTIVE->value)->comment('The status of the rule, e.g., "Active" or "Inactive".');
            $table->timestamp(Model::CREATED_AT)->useCurrent()->comment('The timestamp of when the entry was created.');
            $table->timestamp(Model::UPDATED_AT)->useCurrent()->useCurrentOnUpdate()->comment('The timestamp of when the entry was last updated.');

            $table->primary(['validator_id', 'rule_id']);

            $table->foreign('validator_id')
                ->references('validator_id')
                ->on('v_validators')
                ->onDelete('cascade');

            $table->foreign('rule_id')
                ->references('rule_id')
                ->on('v_rules')
                ->onDelete('cascade');
        });
    }

    /** Reverse the migrations. */
    public function down(): void
    {
        Schema::dropIfExists('v_validator_rules');
        Schema::dropIfExists('v_validators');
        Schema::dropIfExists('v_rules');
    }
};

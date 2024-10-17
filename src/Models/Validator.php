<?php

namespace ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ThreeLeaf\ValidationEngine\Constants\ValidatorEngineConstants;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;

/**
 * Validator model for managing named validation configurations.
 *
 * @property string             $validator_id  The unique ID of the validator
 * @property string             $name          The unique name of the validator
 * @property string|null        $description   A brief description of the validator
 * @property string|null        $context       The context of the validator (e.g., can be used to help retrieve a set of related validators)
 * @property ActiveStatus       $active_status Whether the validator is currently active.
 * @property-read HasMany<Rule> $rules         The rules associated with this validator.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     schema="Validator",
 *     required={"validator_id", "name"},
 *     @OA\Property(
 *         property="validator_id",
 *         type="string",
 *         format="uuid",
 *         description="The unique ID of the validator",
 *         example="3f39e1b8-2d36-49cf-a567-12345abcde67"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="The unique name of the validator",
 *         example="StateAndTimeValidator"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="A brief description of the validator",
 *         example="Validates state and checks time for Monday business hours"
 *     ),
 *     @OA\Property(
 *         property="context",
 *         type="string",
 *         nullable=true,
 *         description="The context of the validator",
 *         example="verify-customer-data"
 *     ),
 *     @OA\Property(
 *         property="active_status",
 *         ref="#/components/schemas/ActiveStatus",
 *         description="The active status of the validator, indicating whether it is active or inactive."
 *     ),
 * )
 */
class Validator extends Model
{

    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = ValidatorEngineConstants::TABLE_PREFIX . 'validators';

    public const PRIMARY_KEY = 'validator_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'name',
        'description',
        'context',
        'active_status',
    ];

    /**
     * Get the rules associated with the validator.
     *
     * @return BelongsToMany<Rule>
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(Rule::class, ValidatorRule::TABLE_NAME, self::PRIMARY_KEY, Rule::PRIMARY_KEY);
    }
}

<?php

namespace ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ThreeLeaf\ValidationEngine\Constants\ValidatorEngineConstants;
use ThreeLeaf\ValidationEngine\Enums\ActiveStatus;

/**
 * ValidatorRule model that represents the relationship between Validators and Rules.
 *
 * @property string                    $validator_rule_id   The unique ID
 * @property string                    $validator_id        The unique ID of the {@link Validator}
 * @property string                    $rule_id             The unique ID of the {@link Rule}
 * @property int                       $order_number        The order in which the rule should be applied
 * @property ActiveStatus              $active_status       Whether the rule is currently active in the validator
 * @property-read BelongsTo<Validator> $validator           The validator that owns this relationship.
 * @property-read BelongsTo<Rule>      $rule                The rule that belongs to this relationship.
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     schema="ValidatorRule",
 *     required={"validator_id", "rule_id", "order_number", "active_status"},
 *     @OA\Property(
 *         property="validator_rule_id",
 *         type="string",
 *         format="uuid",
 *         description="The unique identifier for the ValidatorRule.",
 *         example="3f39e1b8-2d36-49cf-a567-12345abcde67"
 *     ),
 *     @OA\Property(
 *         property="validator_id",
 *         type="string",
 *         format="uuid",
 *         description="The unique ID of the validator associated with this rule.",
 *         example="123e4567-e89b-12d3-a456-426614174000"
 *     ),
 *     @OA\Property(
 *         property="rule_id",
 *         type="string",
 *         format="uuid",
 *         description="The unique ID of the rule associated with this validator.",
 *         example="456e7890-e89b-12d3-a456-426614174001"
 *     ),
 *     @OA\Property(
 *         property="order_number",
 *         type="integer",
 *         description="The order in which the rule should be applied.",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="active_status",
 *         ref="#/components/schemas/ActiveStatus",
 *         description="Indicates whether the rule is currently active or inactive in the validator."
 *     )
 * )
 */
class ValidatorRule extends Model
{
    use HasUuids;

    public const TABLE_NAME = ValidatorEngineConstants::TABLE_PREFIX . 'validator_rules';
    public const PRIMARY_KEY = 'validator_rule_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'validator_id',
        'rule_id',
        'order_number',
        'active_status',
    ];

    /**
     * Get the validator that owns this relationship.
     *
     * @return BelongsTo<Validator>
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(Validator::class, Validator::PRIMARY_KEY, Validator::PRIMARY_KEY);
    }

    /**
     * Get the rule that belongs to this relationship.
     *
     * @return BelongsTo<Rule>
     */
    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class, Rule::PRIMARY_KEY, Rule::PRIMARY_KEY);
    }
}

<?php

namespace ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ThreeLeaf\ValidationEngine\Constants\ValidatorEngineConstants;

/**
 * Rule model for managing validation rule configurations.
 *
 * @property string $rule_id    The unique ID of the rule
 * @property string $attribute  The attribute being validated, e.g., 'state' or 'date_time'
 * @property string $rule_type  The type or class of the rule, e.g., '\ThreeLeaf\ValidationEngine\Rules\EnumRule', '\ThreeLeaf\ValidationEngine\Rules\DayTimeRule'
 * @property string $parameters JSON-encoded parameters specific to the rule type
 *
 * @mixin Builder
 *
 * @OA\Schema(
 *     schema="Rule",
 *     required={"rule_id", "attribute", "rule_type", "parameters"},
 *     @OA\Property(
 *         property="rule_id",
 *         type="string",
 *         format="uuid",
 *         description="The UUID primary key of the rule",
 *         example="123e4567-e89b-12d3-a456-426614174000"
 *     ),
 *     @OA\Property(
 *         property="attribute",
 *         type="string",
 *         description="The attribute being validated, e.g., 'state' or 'date_time'",
 *         example="state"
 *     ),
 *     @OA\Property(
 *         property="rule_type",
 *         type="string",
 *         description="The type or class of the rule, e.g., '\ThreeLeaf\ValidationEngine\Rules\EnumRule'",
 *         example="EnumRule"
 *     ),
 *     @OA\Property(
 *         property="parameters",
 *         type="object",
 *         description="JSON-encoded parameters specific to the rule type",
 *         example={
 *             "enum_class": "\ThreeLeaf\ValidationEngine\Enums\DayOfWeek",
 *             "allowed_values": ["Monday", "Wednesday", "Friday"]
 *         }
 *     ),
 * )
 *
 * @package ThreeLeaf\Validation\Models
 */
class Rule extends Model
{

    use HasUuids;
    use HasFactory;

    public const TABLE_NAME = ValidatorEngineConstants::TABLE_PREFIX . 'rules';

    public const PRIMARY_KEY = 'rule_id';

    protected $table = self::TABLE_NAME;

    protected $primaryKey = self::PRIMARY_KEY;

    protected $fillable = [
        'attribute',
        'rule_type',
        'parameters',
    ];
}

<?php

namespace ThreeLeaf\ValidationEngine\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use ThreeLeaf\ValidationEngine\Casts\ClassCast;
use ThreeLeaf\ValidationEngine\Constants\ValidatorEngineConstants;
use ThreeLeaf\ValidationEngine\Rules\ValidationEngineRule;

/**
 * Rule model for managing validation rule configurations.
 *
 * @property string                      $rule_id    The unique ID of the rule
 * @property string                      $attribute  The attribute being validated, e.g., 'state' or 'date_time'
 * @property string|ValidationEngineRule $rule_type  The fully qualified class name of the rule, which must extend {@link ValidationEngineRule}
 * @property array                       $parameters JSON-encoded parameters specific to the rule type
 *
 * @mixin Builder
 * @method static static create(array $attributes = []) Create a new model instance.
 * @method static static find(mixed $id) Find a model by its primary key.
 * @method static static query() Begin querying the model.
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
 *         description="The fully qualified class name of the rule, which must extend ValidationEngineRule",
 *         example="EnumRule"
 *     ),
 *     @OA\Property(
 *         property="parameters",
 *         type="object",
 *         description="JSON-encoded parameters specific to the rule type",
 *         example={"enum_class": "\\ThreeLeaf\\ValidationEngine\\Enums\\DayOfWeek", "allowed_values": {"Monday", "Wednesday", "Friday"}}
 *     )
 * )
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

    protected $casts = [
        'rule_type' => ClassCast::class . ':' . ValidationEngineRule::class,
        'parameters' => 'json',
    ];

    /**
     * Instantiate the corresponding ValidationEngineRule using the stored rule_type and parameters.
     *
     * @return ValidationEngineRule
     * @throws InvalidArgumentException
     */
    public function instantiateRule(): ValidationEngineRule
    {
        if (!class_exists($this->rule_type) || !is_subclass_of($this->rule_type, ValidationEngineRule::class)) {
            throw new InvalidArgumentException("Invalid rule type: $this->rule_type must extend ValidationEngineRule.");
        }

        return $this->rule_type::make($this->parameters);
    }
}

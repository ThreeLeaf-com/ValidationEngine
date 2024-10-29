<?php

namespace ThreeLeaf\ValidationEngine\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasAttributes;

/**
 * Trait HasCompositeKey
 *
 * Provides support for composite primary keys in Eloquent models.
 * This trait allows models with composite primary keys to perform save and update
 * operations by setting the primary keys for a save query.
 *
 * @mixin HasAttributes
 */
trait HasCompositeKey
{

    /**
     * Retrieve composite key names defined in the model.
     *
     * This method provides access to the composite key fields, which should be defined
     * as an array in the `$primaryKey` property of the model using this trait.
     *
     * @return array The list of attribute names that constitute the composite primary key.
     */
    public function getCompositeKeyNames(): array
    {
        return $this->primaryKeys;
    }

    /**
     * Set the keys for a save or update query, handling composite keys.
     *
     * This method builds a query that includes each part of the composite primary key
     * to ensure the correct record is updated or inserted.
     *
     * @param Builder $query The query builder instance used for the save or update operation.
     *
     * @return Builder The modified query builder with conditions for each key in the composite primary key.
     */
    protected function setKeysForSaveQuery($query): Builder
    {
        foreach ($this->getCompositeKeyNames() as $key) {
            $query->where($key, '=', $this->getOriginal($key) ?? $this->getAttribute($key));
        }

        return $query;
    }
}

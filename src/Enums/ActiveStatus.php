<?php

namespace ThreeLeaf\ValidationEngine\Enums;

/**
 * Enum for representing an active or inactive status.
 *
 * @OA\Schema(
 *     schema="ActiveStatus",
 *     type="string",
 *     enum={
 *         "Active",
 *         "Inactive"
 *     },
 *     description="Represents the active status"
 * )
 */
enum ActiveStatus: string
{
    /** Item is active. */
    case ACTIVE = 'Active';

    /** Item is inactive. */
    case INACTIVE = 'Inactive';
}

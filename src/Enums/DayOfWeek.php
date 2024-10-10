<?php

namespace ThreeLeaf\ValidationEngine\Enums;

/**
 * Enum for representing days of the week.
 *
 * @OA\Schema(
 *     schema="DayOfWeek",
 *     type="string",
 *     enum={
 *         "Monday",
 *         "Tuesday",
 *         "Wednesday",
 *         "Thursday",
 *         "Friday",
 *         "Saturday",
 *         "Sunday",
 *         "Weekend",
 *         "Weekday",
 *         "All"
 *     },
 *     description="Represents the day of the week or a group of days"
 * )
 */
enum DayOfWeek: string
{
    case Monday = 'Monday';
    case Tuesday = 'Tuesday';
    case Wednesday = 'Wednesday';
    case Thursday = 'Thursday';
    case Friday = 'Friday';
    case Saturday = 'Saturday';
    case Sunday = 'Sunday';
    case Weekend = 'Weekend';
    case Weekday = 'Weekday';
    case All = 'All';
}

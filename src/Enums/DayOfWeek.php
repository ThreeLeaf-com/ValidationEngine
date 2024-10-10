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
    case MONDAY = 'Monday';
    case TUESDAY = 'Tuesday';
    case WEDNESDAY = 'Wednesday';
    case THURSDAY = 'Thursday';
    case FRIDAY = 'Friday';
    case SATURDAY = 'Saturday';
    case SUNDAY = 'Sunday';
    case WEEKEND = 'Weekend';
    case WEEKDAY = 'Weekday';
    case ALL = 'All';
}

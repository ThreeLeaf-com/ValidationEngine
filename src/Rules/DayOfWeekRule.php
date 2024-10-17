<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;

/**
 * A validation rule that checks if a given date falls within a specified day of the week,
 * with support for various day types such as specific days (e.g., "Monday", "Weekend", "Weekday", or "All").
 *
 * @property DayOfWeek $dayOfWeek The day of the week to validate against
 * @property string    $timezone  The timezone to be used for validation
 *
 * Example usage:
 *
 * - Validate a specific day (e.g., Monday):
 *   `new DayOfWeekRule(DayOfWeek::Monday)`
 *
 * - Validate a range for weekends:
 *   `new DayOfWeekRule(DayOfWeek::Weekend)`
 */
class DayOfWeekRule extends ValidationEngineRule
{
    /**
     * Create a new DayOfWeekRule instance.
     *
     * @param DayOfWeek $dayOfWeek The day of the week to validate against
     * @param string    $timezone  The timezone to be used for validation, defaults to 'UTC'
     */
    public function __construct(DayOfWeek $dayOfWeek, string $timezone = 'UTC')
    {
        // Use magic setter to assign attributes to the abstract class properties
        $this->dayOfWeek = $dayOfWeek;
        $this->timezone = $timezone;
    }

    /**
     * Validate if the given value falls on the specified day of the week.
     *
     * {@inheritDoc}
     *
     * @param string  $attribute The name of the attribute being validated.
     * @param mixed   $value     The date: Expected to be a Carbon instance or a date string.
     * @param Closure $fail      The closure to call if validation fails.
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $value = Carbon::now($this->timezone);
        } else {
            $value = Carbon::parse($value)->setTimezone($this->timezone);
        }

        $currentDay = $value->format('l');

        if (!$this->dayMatches($currentDay)) {
            $fail("The $attribute is not within the allowed day-of-week: {$this->dayOfWeek->value}.");
        }
    }

    /**
     * Check if the current day matches the rule's day of the week.
     *
     * @param string $currentDay The full textual representation of the day (e.g., "Monday").
     *
     * @return bool True if the day matches the rule's condition, otherwise false.
     */
    protected function dayMatches(string $currentDay): bool
    {
        $weekendDays = ['Saturday', 'Sunday'];
        $weekdayDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return match ($this->dayOfWeek) {
            DayOfWeek::ALL => true,
            DayOfWeek::WEEKEND => in_array($currentDay, $weekendDays),
            DayOfWeek::WEEKDAY => in_array($currentDay, $weekdayDays),
            default => $currentDay === $this->dayOfWeek->value,
        };
    }
}

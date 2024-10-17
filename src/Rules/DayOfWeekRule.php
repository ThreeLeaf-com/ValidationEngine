<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;

/**
 * A validation rule that checks if a given date falls within a specified day of the week,
 * with support for various day types such as specific days (e.g., "Monday"), "Weekend", "Weekday", or "All".
 *
 * Example usage:
 *
 * - Validate a specific day (e.g., Monday):
 *   `new DayOfWeekRule(DayOfWeek::Monday)`
 *
 * - Validate a range for weekends:
 *   `new DayOfWeekRule(DayOfWeek::Weekend)`
 *
 * @mixin ValidationRule
 */
class DayOfWeekRule implements ValidationRule
{
    protected DayOfWeek $dayOfWeek;

    /**
     * Create a new DayOfWeekRule instance.
     *
     * @param DayOfWeek $dayOfWeek The day of the week to validate against
     */
    public function __construct(DayOfWeek $dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * Validate the given attribute.
     *
     * @param string  $attribute The name of the attribute being validated
     * @param mixed   $value     The date: Expected to be a Carbon instance or a date string
     * @param Closure $fail      The Closure to call if validation fails
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $value = Carbon::now();
        } else {
            $value = Carbon::parse($value);
        }

        $currentDay = $value->format('l');

        if (!$this->dayMatches($currentDay)) {
            $fail("The $attribute is not within the allowed day-of-week: {$this->dayOfWeek->value}.");
        }
    }

    /**
     * Check if the current day matches the rule's day of the week.
     *
     * @param string $currentDay
     *
     * @return bool
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

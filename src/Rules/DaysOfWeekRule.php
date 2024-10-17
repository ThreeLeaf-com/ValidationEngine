<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;

/**
 * A validation rule that checks if a given date falls within one of the specified days of the week,
 * with support for an array of day types such as "Monday", "Weekend", "Weekday", or "All".
 *
 * @property DayOfWeek[] $daysOfWeek The array of days of the week to validate against
 * @property string      $timezone   The timezone to be used for validation
 *
 * Example usage:
 *
 * - Validate a specific set of days (e.g., Monday and Wednesday):
 *   `new DaysOfWeekRule([DayOfWeek::Monday, DayOfWeek::Wednesday])`
 *
 * - Validate a range for weekends:
 *   `new DaysOfWeekRule([DayOfWeek::Weekend])`
 */
class DaysOfWeekRule extends ValidationEngineRule
{
    /**
     * Create a new DaysOfWeekRule instance.
     *
     * @param DayOfWeek[] $daysOfWeek The array of days of the week to validate against
     * @param string      $timezone   The timezone to be used for validation, defaults to 'UTC'
     */
    public function __construct(array $daysOfWeek, string $timezone = 'UTC')
    {
        $this->daysOfWeek = $daysOfWeek;
        $this->timezone = $timezone;
    }

    /**
     * Validate if the given value falls on one of the specified days of the week.
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

        foreach ($this->daysOfWeek as $dayOfWeek) {
            $dayOfWeekRule = new DayOfWeekRule($dayOfWeek, $this->timezone);

            $failed = false;
            $dayOfWeekRule->validate($attribute, $value, function () use (&$failed) {
                $failed = true;
            });

            if (!$failed) {
                return;
            }
        }

        $fail("The $attribute does not match any of the allowed days of the week.");
    }
}

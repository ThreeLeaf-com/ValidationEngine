<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;

/**
 * A combined validation rule that checks if a given date and time fall within a
 * specified day of the week and time range.
 *
 * Example usage:
 *
 * - Validate a time range on Mondays from 09:00 to 17:00 in UTC:
 *   `new DayTimeRule(DayOfWeek::Monday, '09:00', '17:00', 'UTC')`
 *
 * - Validate a time range on weekends from 08:00 to 20:00 in America/New_York:
 *   `new DayTimeRule(DayOfWeek::Weekend, '08:00', '20:00', 'America/New_York')`
 *
 * Example with manual fail Closure:
 *
 * ```php
 * // Manually define a fail Closure to handle validation failures
 * $failed = false;
 * $message = '';
 *
 * $fail = function ($msg) use (&$failed, &$message) {
 *     $failed = true; // Set failed to true to indicate a validation failure
 *     $message = $msg; // Store the failure message
 * };
 *
 * // Simulate validating a value
 * $value = '2024-10-14 18:00:00'; // A time outside the allowed range on a Monday
 * $rule->validate('date_time', $value, $fail);
 *
 * // Check the outcome
 * if ($failed) {
 *     echo "Validation failed: {$message}";
 * } else {
 *     echo 'Validation passed.';
 * }
 * ```
 *
 * Alternative example using Illuminate\Support\Facades\Validator:
 *
 * ```php
 * $rule = new DayTimeRule(DayOfWeek::Monday, '09:00', '17:00', 'America/New_York');
 * $validator = Validator::make(
 *     ['dateTime' => '2024-10-14 13:00:00 UTC'],
 *     ['dateTime' => [$rule]]
 * );
 *
 * if ($validator->fails()) {
 *     echo "Validation failed: " . $validator->errors()->first('dateTime');
 * } else { // $validator->passes()
 *     echo "Validation passed.";
 * }
 * ```
 *
 * @see DayOfWeekRule
 * @see TimeOfDayRule
 *
 * @mixin ValidationRule
 */
class DayTimeRule implements ValidationRule
{
    private DayOfWeekRule $dayOfWeekRule;

    private TimeOfDayRule $timeOfDayRule;

    /**
     * Create a new DayTimeRule instance.
     *
     * The default day and time range, if no parameters are used, is 24/7 (24 hours a day, 7 days a week).
     *
     *
     * @param DayOfWeek $dayOfWeek The day of the week to validate against
     * @param string    $startTime The start time (HH:MM format)
     * @param string    $endTime   The end time (HH:MM format)
     * @param string    $timezone  The timezone to be used for validation
     */
    public function __construct(
        DayOfWeek $dayOfWeek = DayOfWeek::ALL,
        string    $startTime = '00:00',
        string    $endTime = '23:59',
        string    $timezone = 'UTC',
    )
    {
        $this->dayOfWeekRule = new DayOfWeekRule($dayOfWeek);
        $this->timeOfDayRule = new TimeOfDayRule($startTime, $endTime, $timezone);
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
        $this->dayOfWeekRule->validate($attribute, $value, $fail);
        $this->timeOfDayRule->validate($attribute, $value, $fail);
    }
}

<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;
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
 * $failed = false;
 * $message = '';
 * $fail = function ($msg) use (&$failed, &$message) {
 *     $failed = true;
 *     $message = $msg;
 * };
 * $value = '2024-10-14 18:00:00';
 * $rule->validate('date_time', $value, $fail);
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
 * } else {
 *     echo "Validation passed.";
 * }
 * ```
 *
 * @see DayOfWeekRule
 * @see TimeOfDayRule
 */
class DayTimeRule extends ValidationEngineRule
{
    /**
     * @var DayOfWeekRule The rule that validates the day of the week.
     */
    private DayOfWeekRule $dayOfWeekRule;

    /**
     * @var TimeOfDayRule The rule that validates the time of the day.
     */
    private TimeOfDayRule $timeOfDayRule;

    /**
     * Create a new DayTimeRule instance.
     *
     * The default day and time range, if no parameters are used, is 24/7 (24 hours a day, 7 days a week).
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
        // Initialize the individual day and time rules
        $this->dayOfWeekRule = new DayOfWeekRule($dayOfWeek, $timezone);
        $this->timeOfDayRule = new TimeOfDayRule($startTime, $endTime, $timezone);
    }

    /**
     * Validate if the given value falls within the specified day of the week and time range.
     *
     * {@inheritDoc}
     *
     * @param string  $attribute The name of the attribute being validated
     * @param mixed   $value     The date: Expected to be a Carbon instance or a date string
     * @param Closure $fail      The Closure to call if validation fails
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate the day of the week
        $this->dayOfWeekRule->validate($attribute, $value, $fail);

        // Validate the time of the day
        $this->timeOfDayRule->validate($attribute, $value, $fail);
    }

    /**
     * Convert the properties of both DayOfWeekRule and TimeOfDayRule into an array.
     *
     * @return array An array of the combined properties from the day and time rules.
     */
    public function toArray(): array
    {
        // Merge the arrays from both rules for unified access
        return array_merge($this->dayOfWeekRule->toArray(), $this->timeOfDayRule->toArray());
    }
}

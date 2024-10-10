<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use ThreeLeaf\ValidationEngine\Enums\DayOfWeek;

/**
 * A validation rule that checks if a given date and time falls within
 * a specified day of the week and a time range, with support for various
 * day types such as specific days (e.g., "Monday"), "Weekend", "Weekday",
 * or "All". The rule also allows setting a specific timezone for the validation.
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
 */
class DayTimeRule implements ValidationRule
{
    protected DayOfWeek $dayOfWeek;

    protected string $startTime;

    protected string $endTime;

    protected string $timezone;

    /**
     * Create a new rule instance.
     *
     * The default day and time range, if no parameters are used, is 24/7 (24 hours a day, 7 days a week).
     *
     * @param DayOfWeek $dayOfWeek The day of the week enum (e.g., DayOfWeek::Monday)
     * @param string    $startTime Start time in 'H:i' format (e.g., '09:00')
     * @param string    $endTime   End time in 'H:i' format (e.g., '17:00')
     * @param string    $timezone  Timezone (e.g., 'America/New_York')
     */
    public function __construct(
        DayOfWeek $dayOfWeek = DayOfWeek::ALL,
        string    $startTime = '00:00',
        string    $endTime = '23:59',
        string    $timezone = 'UTC',
    )
    {
        $this->dayOfWeek = $dayOfWeek;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->timezone = $timezone;
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
            $value = Carbon::now($this->timezone);
        } else {
            $value = Carbon::parse($value)->setTimezone($this->timezone);
        }

        $currentDay = $value->format('l');

        if (!$this->dayMatches($currentDay)) {
            $fail("The $attribute is not within the allowed day-of-week: {$this->dayOfWeek->value}.");
            return;
        }

        $currentTime = $value->format('H:i');

        if ($currentTime < $this->startTime || $currentTime > $this->endTime) {
            $fail("The $attribute is not within the allowed time window from $this->startTime to $this->endTime.");
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

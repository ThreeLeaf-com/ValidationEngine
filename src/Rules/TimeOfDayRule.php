<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;

/**
 * A validation rule that checks if a given time falls within a specified time range,
 * allowing setting a specific timezone for the validation.
 *
 * @property string $startTime The start time in HH:MM format
 * @property string $endTime   The end time in HH:MM format
 * @property string $timezone  The timezone to be used for validation
 *
 * Example usage:
 *
 * - Validate a time range from 09:00 to 17:00 in UTC:
 *   `new TimeOfDayRule('09:00', '17:00', 'UTC')`
 */
class TimeOfDayRule extends ValidationEngineRule
{
    /**
     * Create a new TimeOfDayRule instance.
     *
     * @param string $startTime The start time in HH:MM format, defaults to '00:00'
     * @param string $endTime   The end time in HH:MM format, defaults to '23:59'
     * @param string $timezone  The timezone to be used for validation, defaults to 'UTC'
     */
    public function __construct(string $startTime = '00:00', string $endTime = '23:59', string $timezone = 'UTC')
    {
        // Use the magic setter methods to assign values to attributes
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->timezone = $timezone;
    }

    /**
     * Validate if the given time falls within the allowed time range.
     *
     * {@inheritDoc}
     *
     * @param string  $attribute The name of the attribute being validated
     * @param mixed   $value     The value to validate, expected to be a Carbon instance or date string
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

        $currentTime = $value->format('H:i');

        /* Trigger failure if the time is outside the allowed window */
        if ($currentTime < $this->startTime || $currentTime > $this->endTime) {
            $fail("The $attribute is not within the allowed time window from $this->startTime to $this->endTime.");
        }
    }
}

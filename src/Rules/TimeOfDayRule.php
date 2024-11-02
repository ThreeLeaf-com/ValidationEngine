<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use Throwable;

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
     * @param string|null $startTime The start time in HH:MM format, defaults to '00:00'
     * @param string|null $endTime   The end time in HH:MM format, defaults to '23:59'
     * @param string|null $timezone  The timezone to be used for validation, defaults to 'UTC'
     */
    public function __construct(?string $startTime = null, ?string $endTime = null, ?string $timezone = null)
    {
        $this->startTime = $startTime === null || trim($startTime) === '' ? '00:00' : $startTime;
        $this->endTime = $endTime === null || trim($endTime) === '' ? '23:59' : $endTime;
        $this->timezone = $timezone === null || trim($timezone) === '' ? 'UTC' : $timezone;
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
        try {
            $value = empty($value) ? Carbon::now($this->timezone) : Carbon::parse($value)->setTimezone($this->timezone);
        } catch (Throwable) {
            $fail("The $attribute is not in a valid time format.");
            return;
        }

        $currentTime = $value->format('H:i');

        /* Trigger failure if the time is outside the allowed window */
        if ($currentTime < $this->startTime || $currentTime > $this->endTime) {
            $fail("The $attribute is not within the allowed time window from $this->startTime to $this->endTime.");
        }
    }
}

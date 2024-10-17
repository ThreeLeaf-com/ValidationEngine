<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A validation rule that checks if a given time falls within a specified time range,
 * allowing setting a specific timezone for the validation.
 *
 * Example usage:
 *
 * - Validate a time range from 09:00 to 17:00 in UTC:
 *   `new TimeOfDayRule('09:00', '17:00', 'UTC')`
 *
 * @mixin ValidationRule
 */
class TimeOfDayRule implements ValidationRule
{
    protected string $startTime;

    protected string $endTime;

    protected string $timezone;

    /**
     * Create a new TimeOfDayRule instance.
     *
     * @param string $startTime The start time (HH:MM format)
     * @param string $endTime   The end time (HH:MM format)
     * @param string $timezone  The timezone to be used for validation
     */
    public function __construct(string $startTime = '00:00', string $endTime = '23:59', string $timezone = 'UTC')
    {
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

        $currentTime = $value->format('H:i');

        if ($currentTime < $this->startTime || $currentTime > $this->endTime) {
            $fail("The $attribute is not within the allowed time window from $this->startTime to $this->endTime.");
        }
    }
}

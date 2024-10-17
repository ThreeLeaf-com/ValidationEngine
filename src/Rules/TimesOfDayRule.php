<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use Closure;

/**
 * A validation rule that checks if a given time falls within any of the specified time ranges.
 * Uses multiple TimeOfDayRule instances to validate against each range.
 *
 * Example usage:
 *
 * - Validate that a time falls within two ranges (09:00 to 12:00, and 14:00 to 18:00):
 *   `new TimesOfDayRule([['09:00', '12:00'], ['14:00', '18:00']], 'UTC')`
 *
 * @see TimeOfDayRule
 */
class TimesOfDayRule extends ValidationEngineRule
{
    /**
     * @var array<TimeOfDayRule> An array of TimeOfDayRule instances.
     */
    private array $timeOfDayRules = [];

    /**
     * Create a new TimesOfDayRule instance.
     *
     * @param array  $timeRanges An array of time ranges where each range is an array with [start, end].
     * @param string $timezone   The timezone to be used for validation, defaults to 'UTC'
     */
    public function __construct(array $timeRanges, string $timezone = 'UTC')
    {
        /* Initialize TimeOfDayRule instances for each time range */
        foreach ($timeRanges as [$startTime, $endTime]) {
            $this->timeOfDayRules[] = new TimeOfDayRule($startTime, $endTime, $timezone);
        }
    }

    /**
     * Validate if the given value falls within any of the specified time ranges.
     *
     * {@inheritDoc}
     *
     * @param string  $attribute The name of the attribute being validated.
     * @param mixed   $value     The date: Expected to be a Carbon instance or a date string.
     * @param Closure $fail      The Closure to call if validation fails.
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $passes = false;

        foreach ($this->timeOfDayRules as $timeOfDayRule) {
            $failed = false;
            $timeOfDayRule->validate($attribute, $value, function ($message) use (&$failed) {
                $failed = true;
            });

            if (!$failed) {
                $passes = true;
                break;
            }

        }

        if (!$passes) {
            $fail("The $attribute is not within any of the allowed time ranges.");
        }
    }

    /**
     * Convert the properties of the time rules into an array.
     *
     * @return array An array of the combined properties from the time rules.
     */
    public function toArray(): array
    {
        return array_map(function (TimeOfDayRule $rule) {
            return $rule->toArray();
        }, $this->timeOfDayRules);
    }
}

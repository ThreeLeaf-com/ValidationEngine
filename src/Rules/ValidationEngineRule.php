<?php

namespace ThreeLeaf\ValidationEngine\Rules;

use ArrayAccess;
use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use JsonSerializable;
use ReflectionClass;
use ReflectionException;

/**
 * Abstract class providing base functionality for validation rules.
 * Implements validation, array, and JSON interfaces, and provides magic
 * getter and setter methods for handling rule attributes dynamically.
 *
 * This class also supports basic validation check using the isValidFor() method,
 * which mimics the standard Laravel validation structure.
 *
 * @implements ValidationRule
 * @implements Arrayable
 * @implements ArrayAccess
 * @implements JsonSerializable
 * @implements Jsonable
 */
abstract class ValidationEngineRule implements ValidationRule, Arrayable, ArrayAccess, JsonSerializable, Jsonable
{
    /**
     * Array of attributes used for dynamic property handling within the rule.
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * Create a new instance of the class using an array of attributes.
     *
     * This method uses reflection to dynamically construct the class instance and set its properties.
     * The constructor arguments should match the provided attributes.
     *
     * @param array $attributes The attributes to set for the rule.
     *
     * @return static A new instance of the called class.
     *
     * @throws InvalidArgumentException if the class cannot be constructed from the attributes.
     */
    public static function make(array $attributes): static
    {
        $class = static::class;

        try {
            $reflection = new ReflectionClass($class);
            $constructor = $reflection->getConstructor();

            if ($constructor) {
                $params = $constructor->getParameters();
                $args = [];

                foreach ($params as $param) {
                    $name = $param->getName();
                    if (array_key_exists($name, $attributes)) {
                        $args[] = $attributes[$name];
                    } elseif ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                    } else {
                        throw new InvalidArgumentException("Missing required attribute: $name");
                    }
                }

                $instance = $reflection->newInstanceArgs($args);
            } else {
                $instance = new $class();
            }

            foreach ($attributes as $key => $value) {
                $instance->$key = $value;
            }

            return $instance;
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Unable to create instance of $class: " . $e->getMessage());
        }
    }

    /**
     * Check if an attribute exists in the attributes array.
     *
     * @param mixed $offset The offset to check for existence.
     *
     * @return bool True if the offset exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Retrieve the value of an attribute from the attributes array.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed|null The value at the offset or null if it doesn't exist.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Set an attribute in the attributes array.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset an attribute in the attributes array.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the attributes to an array.
     *
     * @return array The array representation of the attributes.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the attributes to a format suitable for JSON serialization.
     *
     * @return array The data that should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->attributes;
    }

    /**
     * Convert the attributes to a JSON string.
     *
     * @param int $options JSON encoding options.
     *
     * @return string JSON representation of the attributes.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->attributes, $options);
    }

    /**
     * Magic method to dynamically get an attribute.
     *
     * @param string $name The attribute name to get.
     *
     * @return mixed|null The attribute value or null if it doesn't exist.
     */
    public function __get(string $name)
    {
        return $this->attributes[$name] ?? null;
    }

    /**
     * Magic method to dynamically set an attribute.
     *
     * @param string $name  The attribute name to set.
     * @param mixed  $value The value to set.
     *
     * @return void
     */
    public function __set(string $name, mixed $value)
    {
        $this->attributes[$name] = $value;
    }

    /**
     * Validate the given attribute based on the rule.
     * This method should be implemented by concrete classes.
     *
     * @param string  $attribute The name of the attribute being validated.
     * @param mixed   $value     The value to validate.
     * @param Closure $fail      A Closure to capture failure messages.
     *
     * @return void
     */
    abstract public function validate(string $attribute, mixed $value, Closure $fail): void;

    /**
     * Check if a value passes the validation rule.
     * This mimics the Laravel validation approach where failure triggers messages.
     *
     * @param mixed $value The value to validate.
     *
     * @return bool True if the value passes validation, otherwise false.
     */
    public function isValidFor(mixed $value): bool
    {
        $failed = false;
        $message = null;

        /* Run the validate function with a generic attribute name */
        $this->validate('attribute', $value, $this->createFailClosure($failed, $message));

        return !$failed;
    }

    /**
     * Create a closure to handle validation failure.
     *
     * This closure will capture failure status and messages. It will be used in the
     * validation method to trigger the appropriate failure response.
     *
     * @param bool        $failed  A reference to a variable to capture failure status.
     * @param string|null $message A reference to a variable to capture the error message.
     *
     * @return Closure A closure that accepts an error message and updates failure state.
     */
    protected function createFailClosure(bool &$failed, ?string &$message): Closure
    {
        return function ($msg) use (&$failed, &$message) {
            $failed = true;
            $message = $msg;
        };
    }
}

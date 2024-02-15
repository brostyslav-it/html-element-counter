<?php

/**
 * Class Validator
 *
 * Provides static methods for performing validation based on specified rules.
 */
class Validator
{
    /**
     * @var array The default validation data structure with 'ok' flag and 'errors' array.
     */
    private const DEFAULT_DATA = ['ok' => true, 'errors' => []];

    /**
     * Validates a single rule and updates the validation data accordingly.
     *
     * @param array $data The validation data containing 'ok' flag and 'errors' array.
     * @param bool $errorIf The condition to check for an error.
     * @param string $errorMessage The error message to be added if the condition is true.
     */
    private static function validateRule(array &$data, bool $errorIf, string $errorMessage): void
    {
        if ($errorIf) {
            $data['ok'] = false;
            $data['errors'][] = $errorMessage;
        }
    }

    /**
     * Validates an array of rules and returns the final validation data.
     *
     * @param array $rules An array of validation rules, each rule represented as [condition, errorMessage].
     *
     * @return array The validation data containing 'ok' flag and 'errors' array.
     */
    private static function validateRules(array $rules): array
    {
        $data = self::DEFAULT_DATA;

        for ($i = 0; $i < count($rules); $i++) {
            self::validateRule($data, $rules[$i][0], $rules[$i][1]);
        }

        return $data;
    }

    /**
     * Validates an array of rules and returns the final validation data.
     *
     * @param array $rules An array of validation rules, each rule represented as [condition, errorMessage].
     *
     * @return array The validation data containing 'ok' flag and 'errors' array.
     */
    public static function validate(array $rules): array
    {
        return self::validateRules($rules);
    }
}

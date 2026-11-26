<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Rule-based validation, for example ['email' => 'required|email|max:150'].
 */
final class Validator
{
    public static function check(array $data, array $rules, array $labels = []): array
    {
        $clean = [];
        $errors = [];
        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? '';
            $value = is_string($value) ? $value : '';
            $label = $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $argument] = array_pad(explode(':', $rule, 2), 2, '');
                $message = self::apply($name, $argument, $value, $label, $data);
                if ($message !== null) {
                    $errors[$field] = $message;
                    break;
                }
            }
            $clean[$field] = $value;
        }
        if ($errors) {
            throw new ValidationError($errors);
        }

        return $clean;
    }

    private static function apply(string $rule, string $argument, string $value, string $label, array $data): ?string
    {
        if ($value === '') {
            return $rule === 'required' ? "$label is required." : null;
        }
        switch ($rule) {
            case 'required':
                return null;
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) ? null : "$label must be a valid email address.";
            case 'min':
                return mb_strlen($value) >= (int) $argument ? null : "$label must be at least $argument characters.";
            case 'max':
                return mb_strlen($value) <= (int) $argument ? null : "$label may not be longer than $argument characters.";
            case 'username':
                return preg_match('/^[A-Za-z0-9_]{3,30}$/', $value) ? null : "$label must be 3-30 letters, numbers or underscores.";
            case 'phone':
                return preg_match('/^\+?[0-9][0-9 \-]{5,18}$/', $value) ? null : "$label must be a valid phone number.";
            case 'price':
                return preg_match('/^\d{1,6}(\.\d{1,2})?$/', $value) && (float) $value > 0 ? null : "$label must be a positive amount.";
            case 'in':
                return in_array($value, explode(',', $argument), true) ? null : "$label has an invalid value.";
            case 'same':
                return hash_equals((string) ($data[$argument] ?? ''), $value) ? null : "$label does not match.";
            default:
                throw new \InvalidArgumentException("Unknown validation rule: $rule");
        }
    }
}

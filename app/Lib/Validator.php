<?php

namespace App\Lib;

class Validator
{
    private $errors = [];
    private $data = [];
    private $rules = [];

    public function validate($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->errors = [];

        foreach ($rules as $field => $rule) {
            $this->validateField($field, $rule);
        }

        return empty($this->errors);
    }

    private function validateField($field, $rule)
    {
        $value = $this->data[$field] ?? null;
        $rules = explode('|', $rule);

        foreach ($rules as $singleRule) {
            $this->applyRule($field, $value, $singleRule);
        }
    }

    private function applyRule($field, $value, $rule)
    {
        if (strpos($rule, ':') !== false) {
            [$ruleName, $ruleValue] = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $ruleValue = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->errors[$field][] = "The {$field} field is required.";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field][] = "The {$field} field must be a valid email address.";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < (int)$ruleValue) {
                    $this->errors[$field][] = "The {$field} field must be at least {$ruleValue} characters.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > (int)$ruleValue) {
                    $this->errors[$field][] = "The {$field} field must not exceed {$ruleValue} characters.";
                }
                break;

            case 'in':
                $allowedValues = explode(',', $ruleValue);
                if (!empty($value) && !in_array($value, $allowedValues)) {
                    $this->errors[$field][] = "The {$field} field must be one of: " . implode(', ', $allowedValues);
                }
                break;

            case 'unique':
                // This would need database connection - for now, skip
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if (!empty($value) && ($this->data[$confirmField] ?? '') !== $value) {
                    $this->errors[$field][] = "The {$field} confirmation does not match.";
                }
                break;
        }
    }

    public function errors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    public function getFirstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    public function getAllErrors()
    {
        $allErrors = [];
        foreach ($this->errors as $field => $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return $allErrors;
    }
}
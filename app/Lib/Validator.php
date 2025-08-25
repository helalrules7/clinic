<?php

namespace App\Lib;

class Validator
{
    private $errors = [];

    public function validate($data, $rules)
    {
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            
            foreach ($rules as $rule) {
                $this->applyRule($field, $rule, $data[$field] ?? null, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $rule, $value, $data)
    {
        $params = [];
        
        if (strpos($rule, ':') !== false) {
            list($rule, $paramString) = explode(':', $rule, 2);
            $params = explode(',', $paramString);
        }

        switch ($rule) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, 'The ' . $field . ' field is required');
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'The ' . $field . ' field must be a valid email address');
                }
                break;

            case 'min':
                $min = (int) $params[0];
                if (!empty($value) && strlen($value) < $min) {
                    $this->addError($field, 'The ' . $field . ' field must be at least ' . $min . ' characters');
                }
                break;

            case 'max':
                $max = (int) $params[0];
                if (!empty($value) && strlen($value) > $max) {
                    $this->addError($field, 'The ' . $field . ' field must not exceed ' . $max . ' characters');
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, 'The ' . $field . ' field must be numeric');
                }
                break;

            case 'integer':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                    $this->addError($field, 'The ' . $field . ' field must be an integer');
                }
                break;

            case 'decimal':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_FLOAT)) {
                    $this->addError($field, 'The ' . $field . ' field must be a decimal number');
                }
                break;

            case 'date':
                if (!empty($value)) {
                    $date = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$date || $date->format('Y-m-d') !== $value) {
                        $this->addError($field, 'The ' . $field . ' field must be a valid date (YYYY-MM-DD)');
                    }
                }
                break;

            case 'time':
                if (!empty($value)) {
                    $time = \DateTime::createFromFormat('H:i:s', $value);
                    if (!$time || $time->format('H:i:s') !== $value) {
                        $this->addError($field, 'The ' . $field . ' field must be a valid time (HH:MM:SS)');
                    }
                }
                break;

            case 'in':
                if (!empty($value) && !in_array($value, $params)) {
                    $this->addError($field, 'The ' . $field . ' field must be one of: ' . implode(', ', $params));
                }
                break;

            case 'unique':
                if (!empty($value)) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    $except = $params[2] ?? null;
                    
                    if (!$this->isUnique($table, $column, $value, $except)) {
                        $this->addError($field, 'The ' . $field . ' value already exists');
                    }
                }
                break;

            case 'exists':
                if (!empty($value)) {
                    $table = $params[0];
                    $column = $params[1] ?? $field;
                    
                    if (!$this->exists($table, $column, $value)) {
                        $this->addError($field, 'The selected ' . $field . ' is invalid');
                    }
                }
                break;

            case 'confirmed':
                $confirmationField = $field . '_confirmation';
                if (!isset($data[$confirmationField]) || $value !== $data[$confirmationField]) {
                    $this->addError($field, 'The ' . $field . ' confirmation does not match');
                }
                break;

            case 'phone':
                if (!empty($value) && !$this->isValidPhone($value)) {
                    $this->addError($field, 'The ' . $field . ' field must be a valid phone number');
                }
                break;

            case 'national_id':
                if (!empty($value) && !$this->isValidNationalId($value)) {
                    $this->addError($field, 'The ' . $field . ' field must be a valid national ID');
                }
                break;
        }
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getFirstError($field = null)
    {
        if ($field) {
            return $this->errors[$field][0] ?? null;
        }
        
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        
        return null;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }

    private function isUnique($table, $column, $value, $except = null)
    {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = ?";
        $params = [$value];
        
        if ($except) {
            $sql .= " AND id != ?";
            $params[] = $except;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }

    private function exists($table, $column, $value)
    {
        $db = \App\Config\Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        $stmt->execute([$value]);
        
        return $stmt->fetchColumn() > 0;
    }

    private function isValidPhone($phone)
    {
        // Basic phone validation for Egyptian numbers
        return preg_match('/^(\+20|0)?1[0-9]{9}$/', $phone);
    }

    private function isValidNationalId($id)
    {
        // Basic Egyptian national ID validation (14 digits)
        return preg_match('/^[0-9]{14}$/', $id);
    }

    public function sanitize($data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
}

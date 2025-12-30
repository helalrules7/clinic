<?php

namespace App\Services;

/**
 * Shared Interface for All Calculators
 * 
 * All calculator implementations must implement this interface
 * to ensure consistent structure and behavior.
 */
interface CalculatorInterface
{
    /**
     * Calculate results based on provided input data
     * 
     * @param array $data Input data specific to the calculator
     * @return array Calculation results
     */
    public function calculate(array $data): array;

    /**
     * Validate input data for this calculator
     * 
     * @param array $data Input data to validate
     * @return array Validation result:
     *   - valid (bool): Whether data is valid
     *   - errors (array): Array of error messages if invalid
     */
    public function validate(array $data): array;

    /**
     * Get the name of this calculator
     * 
     * @return string Calculator name (e.g., "Pediatric IOL Undercorrection", "Corneal Astigmatism")
     */
    public function getCalculatorName(): string;
}


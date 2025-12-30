<?php

namespace App\Services;

/**
 * Interface for IOL Power Calculators
 * 
 * All IOL power calculation formulas must implement this interface
 * to ensure consistent structure and behavior.
 */
interface IOLCalculatorInterface
{
    /**
     * Calculate IOL power based on provided biometric data
     * 
     * @param array $data Input data containing:
     *   - axial_length (float): Axial length in mm
     *   - k1 (float): K1 keratometry reading in diopters
     *   - k2 (float): K2 keratometry reading in diopters
     *   - a_constant (float): A-constant for the IOL
     *   - target_refraction (float): Target post-operative refraction in diopters
     *   - acd (float|null): Anterior Chamber Depth in mm (optional)
     * 
     * @return array Result containing:
     *   - power (float): Calculated IOL power in diopters
     *   - expected_refraction (float): Expected post-operative refraction in diopters
     *   - warnings (array): Array of warning messages if any
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
     * Get the name of this formula
     * 
     * @return string Formula name (e.g., "SRK/T", "Hoffer Q", "Holladay 1")
     */
    public function getFormulaName(): string;
}



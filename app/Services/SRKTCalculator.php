<?php

namespace App\Services;

/**
 * SRK/T IOL Power Calculator
 * 
 * Implements the SRK/T (Sanders-Retzlaff-Kraff/Theoretical) formula
 * for calculating intraocular lens power.
 * 
 * Formula: P = A - 2.5 × AL - 0.9 × K
 * Where: K = (K1 + K2) / 2
 * 
 * A-Constant optimization based on axial length:
 * - AL < 22.0 mm: A = A_constant - 3
 * - AL 22.0-24.5 mm: A = A_constant
 * - AL > 24.5 mm: A = A_constant + 3
 */
class SRKTCalculator implements IOLCalculatorInterface
{
    public function calculate(array $data): array
    {
        $validation = $this->validate($data);
        if (!$validation['valid']) {
            return [
                'power' => null,
                'expected_refraction' => null,
                'warnings' => $validation['errors']
            ];
        }

        $axialLength = (float)$data['axial_length'];
        $k1 = (float)$data['k1'];
        $k2 = (float)$data['k2'];
        $aConstant = (float)$data['a_constant'];
        $targetRefraction = isset($data['target_refraction']) ? (float)$data['target_refraction'] : 0.0;

        // Calculate average K
        $k = ($k1 + $k2) / 2.0;

        // Optimize A-constant based on axial length
        $optimizedA = $this->optimizeAConstant($aConstant, $axialLength);

        // Calculate IOL power: P = A - 2.5 × AL - 0.9 × K
        $iolPower = $optimizedA - (2.5 * $axialLength) - (0.9 * $k);

        // Adjust for target refraction if provided
        if ($targetRefraction != 0.0) {
            // For SRK/T: adjust IOL power by approximately 1.5 D per 1 D refraction change
            $adjustment = $targetRefraction * 1.5;
            $iolPower = $iolPower + $adjustment;
        }

        // Calculate expected refraction using reverse formula
        // Expected Ref = (A - P - 2.5 × AL - 0.9 × K) / 0.9
        // Simplified approximation for expected refraction
        $expectedRefraction = $this->calculateExpectedRefraction($iolPower, $optimizedA, $axialLength, $k);

        // Generate warnings
        $warnings = [];
        if ($axialLength < 22.0) {
            $warnings[] = "Short eye detected. SRK/T may be less accurate. Consider Hoffer Q formula.";
        } elseif ($axialLength > 26.0) {
            $warnings[] = "Long eye detected. SRK/T formula used with optimized A-constant.";
        }

        return [
            'power' => round($iolPower, 2),
            'expected_refraction' => round($expectedRefraction, 2),
            'warnings' => $warnings
        ];
    }

    public function validate(array $data): array
    {
        $errors = [];

        // Required fields
        if (!isset($data['axial_length']) || $data['axial_length'] === '') {
            $errors[] = "Axial Length is required";
        } elseif (!is_numeric($data['axial_length'])) {
            $errors[] = "Axial Length must be a number";
        } elseif ((float)$data['axial_length'] < 15.0 || (float)$data['axial_length'] > 35.0) {
            $errors[] = "Axial Length must be between 15.0 and 35.0 mm";
        }

        if (!isset($data['k1']) || $data['k1'] === '') {
            $errors[] = "K1 is required";
        } elseif (!is_numeric($data['k1'])) {
            $errors[] = "K1 must be a number";
        } elseif ((float)$data['k1'] < 35.0 || (float)$data['k1'] > 50.0) {
            $errors[] = "K1 must be between 35.0 and 50.0 diopters";
        }

        if (!isset($data['k2']) || $data['k2'] === '') {
            $errors[] = "K2 is required";
        } elseif (!is_numeric($data['k2'])) {
            $errors[] = "K2 must be a number";
        } elseif ((float)$data['k2'] < 35.0 || (float)$data['k2'] > 50.0) {
            $errors[] = "K2 must be between 35.0 and 50.0 diopters";
        }

        if (!isset($data['a_constant']) || $data['a_constant'] === '') {
            $errors[] = "A-Constant is required";
        } elseif (!is_numeric($data['a_constant'])) {
            $errors[] = "A-Constant must be a number";
        } elseif ((float)$data['a_constant'] < 110.0 || (float)$data['a_constant'] > 130.0) {
            $errors[] = "A-Constant must be between 110.0 and 130.0";
        }

        // Optional fields
        if (isset($data['target_refraction']) && $data['target_refraction'] !== '') {
            if (!is_numeric($data['target_refraction'])) {
                $errors[] = "Target Refraction must be a number";
            } elseif ((float)$data['target_refraction'] < -5.0 || (float)$data['target_refraction'] > 5.0) {
                $errors[] = "Target Refraction must be between -5.0 and +5.0 diopters";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function getFormulaName(): string
    {
        return "SRK/T";
    }

    /**
     * Optimize A-constant based on axial length
     * 
     * @param float $aConstant Base A-constant
     * @param float $axialLength Axial length in mm
     * @return float Optimized A-constant
     */
    private function optimizeAConstant(float $aConstant, float $axialLength): float
    {
        if ($axialLength < 22.0) {
            return $aConstant - 3.0;
        } elseif ($axialLength > 24.5) {
            return $aConstant + 3.0;
        } else {
            return $aConstant;
        }
    }

    /**
     * Calculate expected post-operative refraction
     * 
     * @param float $iolPower IOL power in diopters
     * @param float $optimizedA Optimized A-constant
     * @param float $axialLength Axial length in mm
     * @param float $k Average K in diopters
     * @return float Expected refraction in diopters
     */
    private function calculateExpectedRefraction(float $iolPower, float $optimizedA, float $axialLength, float $k): float
    {
        // Reverse SRK/T formula: Expected Ref = (A - P - 2.5 × AL - 0.9 × K) / 0.9
        // Simplified approximation
        $predictedPower = $optimizedA - (2.5 * $axialLength) - (0.9 * $k);
        $refractionDifference = ($iolPower - $predictedPower) / 1.5;
        
        return $refractionDifference;
    }
}



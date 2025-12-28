<?php

namespace App\Services;

/**
 * Hoffer Q IOL Power Calculator
 * 
 * Implements the Hoffer Q formula for calculating intraocular lens power.
 * 
 * Formula: P = (1336 / (AL - ACD)) - (1.336 / ((1.336 / K) - ACD))
 * Where: K = (K1 + K2) / 2
 * 
 * This formula is particularly accurate for short eyes (AL < 22.0 mm)
 * and requires Anterior Chamber Depth (ACD) measurement.
 */
class HofferQCalculator implements IOLCalculatorInterface
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
        $targetRefraction = isset($data['target_refraction']) ? (float)$data['target_refraction'] : 0.0;

        // Calculate average K
        $k = ($k1 + $k2) / 2.0;

        // Get ACD or use default
        $acd = isset($data['acd']) && $data['acd'] !== '' ? (float)$data['acd'] : $this->getDefaultACD($axialLength);

        // Calculate IOL power using Hoffer Q formula
        // P = (1336 / (AL - ACD)) - (1.336 / ((1.336 / K) - ACD))
        $n = 1.336; // Refractive index of aqueous/vitreous
        
        $term1 = 1336.0 / ($axialLength - $acd);
        $term2 = $n / (($n / $k) - $acd);
        
        $iolPower = $term1 - $term2;

        // Adjust for target refraction if provided
        if ($targetRefraction != 0.0) {
            $iolPower = $this->adjustForTargetRefraction($iolPower, $targetRefraction, $axialLength, $k, $acd);
        }

        // Calculate expected refraction
        $expectedRefraction = $this->calculateExpectedRefraction($iolPower, $axialLength, $k, $acd);

        // Generate warnings
        $warnings = [];
        if (!isset($data['acd']) || $data['acd'] === '') {
            $warnings[] = "ACD not provided. Using estimated value based on axial length.";
        }
        if ($axialLength < 22.0) {
            $warnings[] = "Short eye detected. Hoffer Q is recommended for this case.";
        } elseif ($axialLength > 26.0) {
            $warnings[] = "Long eye detected. Consider SRK/T formula for better accuracy.";
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

        // Optional ACD validation
        if (isset($data['acd']) && $data['acd'] !== '') {
            if (!is_numeric($data['acd'])) {
                $errors[] = "ACD must be a number";
            } elseif ((float)$data['acd'] < 2.0 || (float)$data['acd'] > 5.0) {
                $errors[] = "ACD must be between 2.0 and 5.0 mm";
            }
        }

        // Optional target refraction
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
        return "Hoffer Q";
    }

    /**
     * Get default ACD based on axial length if not provided
     * 
     * @param float $axialLength Axial length in mm
     * @return float Estimated ACD in mm
     */
    private function getDefaultACD(float $axialLength): float
    {
        // Hoffer's regression formula for ACD estimation
        // ACD = 0.62467 × AL - 68.747
        // But we'll use a simpler approximation
        if ($axialLength < 22.0) {
            return 3.0; // Short eyes typically have shallower ACD
        } elseif ($axialLength > 26.0) {
            return 3.5; // Long eyes typically have deeper ACD
        } else {
            return 3.2; // Normal eyes
        }
    }

    /**
     * Adjust IOL power for target refraction
     * 
     * @param float $iolPower Base IOL power
     * @param float $targetRefraction Target refraction in diopters
     * @param float $axialLength Axial length in mm
     * @param float $k Average K in diopters
     * @param float $acd Anterior chamber depth in mm
     * @return float Adjusted IOL power
     */
    private function adjustForTargetRefraction(float $iolPower, float $targetRefraction, float $axialLength, float $k, float $acd): float
    {
        // Adjustment factor: approximately 1.5 D IOL power change per 1 D refraction change
        $adjustment = $targetRefraction * 1.5;
        return $iolPower + $adjustment;
    }

    /**
     * Calculate expected post-operative refraction
     * 
     * @param float $iolPower IOL power in diopters
     * @param float $axialLength Axial length in mm
     * @param float $k Average K in diopters
     * @param float $acd Anterior chamber depth in mm
     * @return float Expected refraction in diopters
     */
    private function calculateExpectedRefraction(float $iolPower, float $axialLength, float $k, float $acd): float
    {
        $n = 1.336;
        
        // Reverse calculation: what refraction would this IOL power produce?
        // Simplified approximation
        $predictedPower = (1336.0 / ($axialLength - $acd)) - ($n / (($n / $k) - $acd));
        $refractionDifference = ($iolPower - $predictedPower) / 1.5;
        
        return $refractionDifference;
    }
}



<?php

namespace App\Services;

/**
 * Corneal Astigmatism Calculator Service
 * 
 * Calculates corneal astigmatism using vector analysis (double-angle method)
 * and provides surgical recommendations based on net astigmatism magnitude.
 */
class CornealAstigmatismService implements CalculatorInterface
{
    /**
     * Calculate corneal astigmatism with vector analysis
     * 
     * @param array $data Input data containing:
     *   - k1 (float): K1 keratometry reading (D)
     *   - k1_axis (float): K1 axis (0–180 degrees)
     *   - k2 (float): K2 keratometry reading (D)
     *   - k2_axis (float): K2 axis (0–180 degrees)
     *   - sia (float): Surgically Induced Astigmatism (D, optional, default 0)
     *   - sia_axis (float): SIA axis (0–180 degrees, optional, default 0)
     * 
     * @return array Calculation results
     */
    public function calculate(array $data): array
    {
        $validation = $this->validate($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Extract input values (validation already passed, so values are guaranteed to be numeric)
        $k1 = (float)$data['k1'];
        $k1Axis = (float)$data['k1_axis'];
        $k2 = (float)$data['k2'];
        $k2Axis = (float)$data['k2_axis'];
        $sia = isset($data['sia']) && $data['sia'] !== null && $data['sia'] !== '' ? (float)$data['sia'] : 0.0;
        $siaAxis = isset($data['sia_axis']) && $data['sia_axis'] !== null && $data['sia_axis'] !== '' ? (float)$data['sia_axis'] : 0.0;

        // Calculate corneal astigmatism magnitude
        // Note: Typically, astigmatism magnitude = |K1 - K2| at the steeper meridian
        // The axis is typically the axis of the steeper meridian (K1)
        $cornealMagnitude = abs($k1 - $k2);
        $cornealAxis = $k1Axis; // Use K1 axis as the steeper meridian

        // Convert corneal astigmatism to double-angle vector
        $cornealVector = $this->toDoubleAngleVector($cornealMagnitude, $cornealAxis);

        // Apply SIA if provided
        $siaVector = ['x' => 0.0, 'y' => 0.0];
        if ($sia > 0) {
            $siaVector = $this->toDoubleAngleVector($sia, $siaAxis);
        }

        // Add vectors
        $netX = $cornealVector['x'] + $siaVector['x'];
        $netY = $cornealVector['y'] + $siaVector['y'];

        // Convert back to magnitude and axis
        $netMagnitude = sqrt($netX * $netX + $netY * $netY);
        $netAxis = $this->fromDoubleAngleVector($netX, $netY);

        // Determine surgical recommendation
        $recommendation = $this->determineSurgicalRecommendation($netMagnitude);
        $recommendationMessage = $this->getRecommendationMessage($recommendation, $netMagnitude);

        // Generate clinical notes
        $clinicalNotes = $this->generateClinicalNotes($cornealMagnitude, $cornealAxis, $sia, $netMagnitude, $netAxis, $recommendation);

        return [
            'success' => true,
            'corneal_astigmatism' => [
                'magnitude' => round($cornealMagnitude, 2),
                'axis' => round($cornealAxis, 1)
            ],
            'sia_applied' => [
                'magnitude' => round($sia, 2),
                'axis' => round($siaAxis, 1)
            ],
            'net_astigmatism' => [
                'magnitude' => round($netMagnitude, 2),
                'axis' => round($netAxis, 1)
            ],
            'surgical_recommendation' => $recommendation,
            'recommendation_message' => $recommendationMessage,
            'clinical_notes' => $clinicalNotes
        ];
    }

    /**
     * Validate input data
     * 
     * @param array $data Input data to validate
     * @return array Validation result
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Validate K1
        if (!isset($data['k1']) || $data['k1'] === null || $data['k1'] === '') {
            $errors[] = 'K1 keratometry reading is required and must be numeric';
        } elseif (!is_numeric($data['k1'])) {
            $errors[] = 'K1 must be numeric';
        } else {
            $k1 = (float)$data['k1'];
            if ($k1 < 30 || $k1 > 60) {
                $errors[] = 'K1 must be within physiologic range (30-60 D)';
            }
        }

        // Validate K1 axis
        if (!isset($data['k1_axis']) || $data['k1_axis'] === null || $data['k1_axis'] === '') {
            $errors[] = 'K1 axis is required and must be numeric';
        } elseif (!is_numeric($data['k1_axis'])) {
            $errors[] = 'K1 axis must be numeric';
        } else {
            $k1Axis = (float)$data['k1_axis'];
            if ($k1Axis < 0 || $k1Axis > 180) {
                $errors[] = 'K1 axis must be between 0 and 180 degrees';
            }
        }

        // Validate K2
        if (!isset($data['k2']) || $data['k2'] === null || $data['k2'] === '') {
            $errors[] = 'K2 keratometry reading is required and must be numeric';
        } elseif (!is_numeric($data['k2'])) {
            $errors[] = 'K2 must be numeric';
        } else {
            $k2 = (float)$data['k2'];
            if ($k2 < 30 || $k2 > 60) {
                $errors[] = 'K2 must be within physiologic range (30-60 D)';
            }
        }

        // Validate K2 axis
        if (!isset($data['k2_axis']) || $data['k2_axis'] === null || $data['k2_axis'] === '') {
            $errors[] = 'K2 axis is required and must be numeric';
        } elseif (!is_numeric($data['k2_axis'])) {
            $errors[] = 'K2 axis must be numeric';
        } else {
            $k2Axis = (float)$data['k2_axis'];
            if ($k2Axis < 0 || $k2Axis > 180) {
                $errors[] = 'K2 axis must be between 0 and 180 degrees';
            }
        }

        // Validate K2 <= K1 (flatter meridian should be K2)
        if (isset($data['k1']) && isset($data['k2']) && is_numeric($data['k1']) && is_numeric($data['k2'])) {
            if ((float)$data['k2'] > (float)$data['k1']) {
                $errors[] = 'K2 (flatter meridian) must be less than or equal to K1 (steeper meridian)';
            }
        }

        // Validate SIA (optional)
        if (isset($data['sia'])) {
            if (!is_numeric($data['sia'])) {
                $errors[] = 'SIA must be numeric';
            } elseif ((float)$data['sia'] < 0) {
                $errors[] = 'SIA must be greater than or equal to zero';
            }

            // Validate SIA axis if SIA is provided
            if (isset($data['sia_axis'])) {
                if (!is_numeric($data['sia_axis'])) {
                    $errors[] = 'SIA axis must be numeric';
                } elseif ((float)$data['sia_axis'] < 0 || (float)$data['sia_axis'] > 180) {
                    $errors[] = 'SIA axis must be between 0 and 180 degrees';
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get calculator name
     * 
     * @return string Calculator name
     */
    public function getCalculatorName(): string
    {
        return 'Corneal Astigmatism Calculator';
    }

    /**
     * Convert magnitude and axis to double-angle vector coordinates
     * 
     * @param float $magnitude Astigmatism magnitude in diopters
     * @param float $axis Axis in degrees (0-180)
     * @return array Vector coordinates ['x' => float, 'y' => float]
     */
    private function toDoubleAngleVector(float $magnitude, float $axis): array
    {
        // Convert axis to radians
        $axisRadians = deg2rad($axis);
        
        // Double-angle transformation
        $x = $magnitude * cos(2 * $axisRadians);
        $y = $magnitude * sin(2 * $axisRadians);
        
        return ['x' => $x, 'y' => $y];
    }

    /**
     * Convert double-angle vector coordinates back to magnitude and axis
     * 
     * @param float $x X component of double-angle vector
     * @param float $y Y component of double-angle vector
     * @return float Axis in degrees (0-180)
     */
    private function fromDoubleAngleVector(float $x, float $y): float
    {
        // Calculate magnitude
        $magnitude = sqrt($x * $x + $y * $y);
        
        if ($magnitude == 0) {
            return 0.0; // No astigmatism
        }
        
        // Calculate angle from double-angle coordinates
        $angle = atan2($y, $x);
        
        // Convert back from double-angle (divide by 2)
        $axisRadians = $angle / 2.0;
        
        // Convert to degrees
        $axis = rad2deg($axisRadians);
        
        // Normalize to 0-180 degrees
        while ($axis < 0) {
            $axis += 180;
        }
        while ($axis >= 180) {
            $axis -= 180;
        }
        
        return $axis;
    }

    /**
     * Determine surgical recommendation based on net astigmatism magnitude
     * 
     * Clinical Rules:
     * - < 0.75 D → Standard IOL
     * - 0.75–1.5 D → Limbal Relaxing Incisions (LRI)
     * - ≥ 1.5 D → Toric IOL
     * 
     * @param float $magnitude Net astigmatism magnitude in diopters
     * @return string Surgical recommendation
     */
    private function determineSurgicalRecommendation(float $magnitude): string
    {
        if ($magnitude < 0.75) {
            return 'standard_iol';
        } elseif ($magnitude >= 0.75 && $magnitude < 1.5) {
            return 'lri';
        } else {
            return 'toric_iol';
        }
    }

    /**
     * Get recommendation message
     * 
     * @param string $recommendation Surgical recommendation
     * @param float $magnitude Net astigmatism magnitude
     * @return string Recommendation message
     */
    private function getRecommendationMessage(string $recommendation, float $magnitude): string
    {
        switch ($recommendation) {
            case 'standard_iol':
                return "Standard IOL recommended. Net astigmatism ({$magnitude} D) is minimal and can be managed with standard monofocal IOL.";
            case 'lri':
                return "Limbal Relaxing Incisions (LRI) recommended. Net astigmatism ({$magnitude} D) is moderate and can be effectively corrected with LRI combined with standard IOL.";
            case 'toric_iol':
                return "Toric IOL recommended. Net astigmatism ({$magnitude} D) is significant and requires toric IOL implantation for optimal visual outcome.";
            default:
                return "Unable to determine surgical recommendation.";
        }
    }

    /**
     * Generate clinical notes
     * 
     * @param float $cornealMagnitude Corneal astigmatism magnitude
     * @param float $cornealAxis Corneal astigmatism axis
     * @param float $sia SIA magnitude
     * @param float $netMagnitude Net astigmatism magnitude
     * @param float $netAxis Net astigmatism axis
     * @param string $recommendation Surgical recommendation
     * @return string Clinical notes
     */
    private function generateClinicalNotes(float $cornealMagnitude, float $cornealAxis, float $sia, float $netMagnitude, float $netAxis, string $recommendation): string
    {
        $notes = "Corneal astigmatism analysis: ";
        $notes .= "Corneal astigmatism = {$cornealMagnitude} D @ {$cornealAxis}°.";
        
        if ($sia > 0) {
            $notes .= " Surgically Induced Astigmatism (SIA) of {$sia} D has been applied using vector analysis (double-angle method).";
        }
        
        $notes .= " Net astigmatism after SIA adjustment: {$netMagnitude} D @ {$netAxis}°.";
        $notes .= " Surgical recommendation: " . strtoupper($recommendation) . ".";
        $notes .= " Vector analysis ensures accurate astigmatism calculation by properly accounting for axis differences.";
        
        return $notes;
    }
}


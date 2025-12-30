<?php

namespace App\Services;

/**
 * Target IOP Calculator Service
 * 
 * Calculates target intraocular pressure (IOP) based on baseline IOP and glaucoma stage,
 * with optional adjustments for life expectancy and risk factors.
 * 
 * Clinical Logic:
 * - Early glaucoma: 20-25% reduction (midpoint: 22.5%)
 * - Moderate glaucoma: 30-35% reduction (midpoint: 32.5%)
 * - Advanced glaucoma: 40-50% reduction (midpoint: 45%)
 * - High life expectancy: +2.5% additional reduction
 * - Risk factors (each): +1% additional reduction
 */
class TargetIOPCalculatorService implements CalculatorInterface
{
    /**
     * Calculate target IOP based on baseline IOP, stage, and risk factors
     * 
     * @param array $data Input data containing:
     *   - baseline_iop (float): Baseline IOP in mmHg (5-60)
     *   - glaucoma_stage (string): "Early", "Moderate", or "Advanced"
     *   - high_life_expectancy (bool|string): Whether patient has high life expectancy (optional)
     *   - risk_factors (array): Array of risk factor strings (optional)
     *     - "high_myopia"
     *     - "family_history"
     *     - "diabetes"
     *     - "hypertension"
     *     - "previous_surgery"
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

        // Extract and normalize input values
        $baselineIOP = (float)$data['baseline_iop'];
        $stage = $data['glaucoma_stage'];
        
        // Normalize high_life_expectancy (handle string "true"/"false" or boolean)
        $highLifeExpectancy = false;
        if (isset($data['high_life_expectancy'])) {
            $value = $data['high_life_expectancy'];
            if (is_bool($value)) {
                $highLifeExpectancy = $value;
            } elseif (is_string($value)) {
                $highLifeExpectancy = in_array(strtolower($value), ['true', '1', 'on', 'yes']);
            } elseif (is_numeric($value)) {
                $highLifeExpectancy = (bool)$value;
            }
        }

        // Normalize risk factors (handle array or comma-separated string)
        $riskFactors = [];
        if (isset($data['risk_factors'])) {
            if (is_array($data['risk_factors'])) {
                $riskFactors = $data['risk_factors'];
            } elseif (is_string($data['risk_factors']) && !empty($data['risk_factors'])) {
                // Handle comma-separated string
                $riskFactors = array_map('trim', explode(',', $data['risk_factors']));
            }
        }

        // Calculate base reduction percentage based on stage
        $baseReduction = $this->determineReductionPercentage($stage, $highLifeExpectancy, $riskFactors);

        // Apply risk factor adjustments
        $finalReduction = $this->applyRiskFactorAdjustment($baseReduction, $riskFactors);

        // Calculate target IOP
        $targetIOP = $baselineIOP * (1 - $finalReduction / 100);
        $targetIOP = round($targetIOP, 1); // Round to 1 decimal place

        // Generate clinical note
        $clinicalNote = $this->generateClinicalNote($baselineIOP, $targetIOP, $finalReduction, $stage, $highLifeExpectancy, $riskFactors);

        // Build applied rule description
        $appliedRule = $this->getAppliedRuleDescription($stage, $highLifeExpectancy, $riskFactors, $finalReduction);

        return [
            'success' => true,
            'baseline_iop' => round($baselineIOP, 1),
            'target_iop' => $targetIOP,
            'reduction_percentage' => round($finalReduction, 2),
            'glaucoma_stage' => $stage,
            'applied_rule' => $appliedRule,
            'clinical_note' => $clinicalNote
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

        // Validate baseline_iop
        if (!isset($data['baseline_iop']) || $data['baseline_iop'] === null || $data['baseline_iop'] === '') {
            $errors[] = 'Baseline IOP is required and must be numeric';
        } elseif (!is_numeric($data['baseline_iop'])) {
            $errors[] = 'Baseline IOP must be numeric';
        } else {
            $baselineIOP = (float)$data['baseline_iop'];
            if ($baselineIOP < 5 || $baselineIOP > 60) {
                $errors[] = 'Baseline IOP must be within physiologic range (5-60 mmHg)';
            }
        }

        // Validate glaucoma_stage
        if (!isset($data['glaucoma_stage']) || $data['glaucoma_stage'] === null || $data['glaucoma_stage'] === '') {
            $errors[] = 'Glaucoma stage is required';
        } elseif (!in_array($data['glaucoma_stage'], ['Early', 'Moderate', 'Advanced'])) {
            $errors[] = 'Glaucoma stage must be one of: Early, Moderate, Advanced';
        }

        // Validate high_life_expectancy (optional, but if provided should be boolean-like)
        if (isset($data['high_life_expectancy']) && $data['high_life_expectancy'] !== null && $data['high_life_expectancy'] !== '') {
            // Accept boolean, string "true"/"false", or numeric 0/1
            // No strict validation needed as we normalize in calculate()
        }

        // Validate risk_factors (optional, but if provided should be array or valid strings)
        if (isset($data['risk_factors']) && $data['risk_factors'] !== null && $data['risk_factors'] !== '') {
            $validRiskFactors = ['high_myopia', 'family_history', 'diabetes', 'hypertension', 'previous_surgery'];
            $riskFactors = [];
            
            if (is_array($data['risk_factors'])) {
                $riskFactors = $data['risk_factors'];
            } elseif (is_string($data['risk_factors'])) {
                $riskFactors = array_map('trim', explode(',', $data['risk_factors']));
            }
            
            foreach ($riskFactors as $factor) {
                if (!in_array($factor, $validRiskFactors)) {
                    $errors[] = "Invalid risk factor: {$factor}. Valid factors are: " . implode(', ', $validRiskFactors);
                    break;
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
        return 'Target IOP Calculator';
    }

    /**
     * Determine base reduction percentage based on glaucoma stage
     * 
     * Clinical Rules:
     * - Early glaucoma: 20-25% reduction (midpoint: 22.5%)
     * - Moderate glaucoma: 30-35% reduction (midpoint: 32.5%)
     * - Advanced glaucoma: 40-50% reduction (midpoint: 45%)
     * - High life expectancy: +2.5% additional reduction
     * 
     * @param string $stage Glaucoma stage ("Early", "Moderate", or "Advanced")
     * @param bool $highLifeExpectancy Whether patient has high life expectancy
     * @param array $riskFactors Array of risk factor strings (for documentation, adjustment applied separately)
     * @return float Base reduction percentage
     */
    private function determineReductionPercentage(string $stage, bool $highLifeExpectancy, array $riskFactors): float
    {
        // Base reduction by stage (using midpoints of clinical ranges)
        $baseReduction = 0.0;
        
        switch ($stage) {
            case 'Early':
                // Early glaucoma: 20-25% reduction (midpoint: 22.5%)
                $baseReduction = 22.5;
                break;
            case 'Moderate':
                // Moderate glaucoma: 30-35% reduction (midpoint: 32.5%)
                $baseReduction = 32.5;
                break;
            case 'Advanced':
                // Advanced glaucoma: 40-50% reduction (midpoint: 45%)
                $baseReduction = 45.0;
                break;
        }

        // Add high life expectancy adjustment (+2.5%)
        if ($highLifeExpectancy) {
            $baseReduction += 2.5;
        }

        return $baseReduction;
    }

    /**
     * Apply risk factor adjustments to reduction percentage
     * 
     * Each risk factor adds +1% to the reduction percentage.
     * Valid risk factors:
     * - high_myopia
     * - family_history
     * - diabetes
     * - hypertension
     * - previous_surgery
     * 
     * @param float $baseReduction Base reduction percentage
     * @param array $riskFactors Array of risk factor strings
     * @return float Final reduction percentage after risk factor adjustments
     */
    private function applyRiskFactorAdjustment(float $baseReduction, array $riskFactors): float
    {
        // Each risk factor adds +1% to reduction
        $riskFactorAdjustment = count($riskFactors) * 1.0;
        
        return $baseReduction + $riskFactorAdjustment;
    }

    /**
     * Generate clinical note explaining the calculation
     * 
     * @param float $baselineIOP Baseline IOP in mmHg
     * @param float $targetIOP Calculated target IOP in mmHg
     * @param float $reductionPercentage Final reduction percentage applied
     * @param string $stage Glaucoma stage
     * @param bool $highLifeExpectancy Whether high life expectancy adjustment was applied
     * @param array $riskFactors Array of risk factors applied
     * @return string Clinical note
     */
    private function generateClinicalNote(float $baselineIOP, float $targetIOP, float $reductionPercentage, string $stage, bool $highLifeExpectancy, array $riskFactors): string
    {
        $note = "Target IOP calculated for {$stage} glaucoma. ";
        $note .= "A {$reductionPercentage}% reduction from baseline ({$baselineIOP} mmHg) is recommended, ";
        $note .= "resulting in a target IOP of {$targetIOP} mmHg. ";

        // Add life expectancy adjustment note
        if ($highLifeExpectancy) {
            $note .= "Additional 2.5% reduction applied due to high life expectancy. ";
        }

        // Add risk factors note
        if (!empty($riskFactors)) {
            $riskFactorNames = $this->getRiskFactorDisplayNames($riskFactors);
            $riskFactorCount = count($riskFactors);
            $note .= "Risk factors (" . implode(', ', $riskFactorNames) . ") add {$riskFactorCount}% reduction. ";
        }

        $note .= "Final target: {$targetIOP} mmHg. ";
        $note .= "This target aims to slow disease progression and preserve visual function.";

        return $note;
    }

    /**
     * Get human-readable display names for risk factors
     * 
     * @param array $riskFactors Array of risk factor keys
     * @return array Array of display names
     */
    private function getRiskFactorDisplayNames(array $riskFactors): array
    {
        $displayNames = [
            'high_myopia' => 'High myopia',
            'family_history' => 'Family history of glaucoma',
            'diabetes' => 'Diabetes',
            'hypertension' => 'Hypertension',
            'previous_surgery' => 'Previous glaucoma surgery'
        ];

        $names = [];
        foreach ($riskFactors as $factor) {
            if (isset($displayNames[$factor])) {
                $names[] = $displayNames[$factor];
            }
        }

        return $names;
    }

    /**
     * Get description of applied rule
     * 
     * @param string $stage Glaucoma stage
     * @param bool $highLifeExpectancy Whether high life expectancy adjustment was applied
     * @param array $riskFactors Array of risk factors
     * @param float $finalReduction Final reduction percentage
     * @return string Applied rule description
     */
    private function getAppliedRuleDescription(string $stage, bool $highLifeExpectancy, array $riskFactors, float $finalReduction): string
    {
        $description = "{$stage} glaucoma: ";
        
        // Base reduction by stage
        $baseReductions = [
            'Early' => '22.5%',
            'Moderate' => '32.5%',
            'Advanced' => '45%'
        ];
        
        $description .= $baseReductions[$stage] ?? '';

        // Add adjustments
        $adjustments = [];
        if ($highLifeExpectancy) {
            $adjustments[] = '+2.5% (high life expectancy)';
        }
        
        if (!empty($riskFactors)) {
            $adjustments[] = '+' . count($riskFactors) . '% (' . count($riskFactors) . ' risk factor' . (count($riskFactors) > 1 ? 's' : '') . ')';
        }

        if (!empty($adjustments)) {
            $description .= ' ' . implode(', ', $adjustments);
        }

        $description .= " = {$finalReduction}% total reduction";

        return $description;
    }
}

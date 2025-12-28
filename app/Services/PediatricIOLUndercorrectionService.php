<?php

namespace App\Services;

/**
 * Pediatric IOL Undercorrection Calculator Service
 * 
 * Calculates appropriate IOL power undercorrection for pediatric patients
 * based on age-specific clinical guidelines.
 */
class PediatricIOLUndercorrectionService implements CalculatorInterface
{
    /**
     * Calculate pediatric IOL undercorrection
     * 
     * @param array $data Input data containing:
     *   - age_value (float): Age numeric value
     *   - age_unit (string): 'months' or 'years'
     *   - calculated_iol_power (float): IOL power from standard calculator (D)
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

        // Normalize age to years
        $ageValueRaw = $data['age_value'] ?? null;
        $ageUnit = $data['age_unit'] ?? 'years';
        
        // Ensure age_value is numeric
        if ($ageValueRaw === null || $ageValueRaw === '' || !is_numeric($ageValueRaw)) {
            return [
                'success' => false,
                'errors' => ['Age value is required and must be numeric']
            ];
        }
        
        $ageValue = (float)$ageValueRaw;
        
        // Ensure age_unit is valid
        if (!in_array($ageUnit, ['months', 'years'])) {
            $ageUnit = 'years';
        }
        
        $ageYears = $this->normalizeAge($ageValue, $ageUnit);
        $ageMonths = $ageUnit === 'months' 
            ? $ageValue 
            : $ageValue * 12;

        // Determine undercorrection percentage based on age group
        $undercorrectionPercentage = $this->determineUndercorrection($ageYears);
        $ageGroup = $this->getAgeGroup($ageYears);

        // Calculate adjusted IOL power
        $calculatedPowerRaw = $data['calculated_iol_power'] ?? null;
        
        // Ensure calculated_iol_power is numeric
        if ($calculatedPowerRaw === null || $calculatedPowerRaw === '' || !is_numeric($calculatedPowerRaw)) {
            return [
                'success' => false,
                'errors' => ['Calculated IOL power is required and must be numeric']
            ];
        }
        
        $calculatedPower = (float)$calculatedPowerRaw;
        $adjustedPower = $calculatedPower * (1 - $undercorrectionPercentage / 100);

        // Round to nearest 0.5 D step
        $roundedPower = round($adjustedPower * 2) / 2;

        // Generate clinical note
        $clinicalNote = $this->generateClinicalNote($ageYears, $ageGroup, $undercorrectionPercentage, $calculatedPower, $roundedPower);

        return [
            'success' => true,
            'age_years' => round($ageYears, 2),
            'age_months' => round($ageMonths, 1),
            'calculated_iol_power' => round($calculatedPower, 2),
            'undercorrection_percentage' => round($undercorrectionPercentage, 2),
            'adjusted_iol_power' => round($adjustedPower, 2),
            'rounded_iol_power' => round($roundedPower, 2),
            'clinical_note' => $clinicalNote,
            'age_group' => $ageGroup
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

        // Validate age_value
        if (!isset($data['age_value']) || $data['age_value'] === null || $data['age_value'] === '') {
            $errors[] = 'Age value is required and must be numeric';
        } elseif (!is_numeric($data['age_value'])) {
            $errors[] = 'Age value must be numeric';
        } else {
            $ageValue = (float)$data['age_value'];
            if ($ageValue <= 0) {
                $errors[] = 'Age must be greater than zero';
            } elseif ($ageValue > 216) { // 18 years in months
                $errors[] = 'Age exceeds reasonable pediatric range (maximum 18 years)';
            }
        }

        // Validate age_unit
        if (!isset($data['age_unit']) || !in_array($data['age_unit'], ['months', 'years'])) {
            $errors[] = 'Age unit must be either "months" or "years"';
        }

        // Validate calculated_iol_power
        if (!isset($data['calculated_iol_power']) || $data['calculated_iol_power'] === null || $data['calculated_iol_power'] === '') {
            $errors[] = 'Calculated IOL power is required and must be numeric';
        } elseif (!is_numeric($data['calculated_iol_power'])) {
            $errors[] = 'Calculated IOL power must be numeric';
        } else {
            $iolPower = (float)$data['calculated_iol_power'];
            if ($iolPower < 0 || $iolPower > 40) {
                $errors[] = 'IOL power must be within physiologic range (0-40 D)';
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
        return 'Pediatric IOL Undercorrection';
    }

    /**
     * Normalize age to years
     * 
     * @param float|int|string $ageValue Age value
     * @param string|null $ageUnit Age unit ('months' or 'years')
     * @return float Age in years
     */
    private function normalizeAge($ageValue, $ageUnit = null): float
    {
        // Ensure ageValue is numeric
        if (!is_numeric($ageValue)) {
            return 0.0;
        }
        
        $ageValue = (float)$ageValue;
        
        // Default to years if unit is not provided or invalid
        if ($ageUnit === null || !is_string($ageUnit)) {
            $ageUnit = 'years';
        }
        
        if ($ageUnit === 'months') {
            return $ageValue / 12.0;
        }
        
        return $ageValue;
    }

    /**
     * Determine undercorrection percentage based on age
     * 
     * Clinical Rules:
     * - Age < 2 years → 20% undercorrection
     * - Age 2–5 years → 10–15% undercorrection (use midpoint: 12.5%)
     * - Age ≥ 5 years → 5–10% undercorrection (use midpoint: 7.5%)
     * 
     * @param float $ageYears Age in years
     * @return float Undercorrection percentage
     */
    private function determineUndercorrection(float $ageYears): float
    {
        if ($ageYears < 2.0) {
            return 20.0;
        } elseif ($ageYears >= 2.0 && $ageYears < 5.0) {
            return 12.5; // Midpoint of 10-15%
        } else {
            return 7.5; // Midpoint of 5-10%
        }
    }

    /**
     * Get age group description
     * 
     * @param float $ageYears Age in years
     * @return string Age group description
     */
    private function getAgeGroup(float $ageYears): string
    {
        if ($ageYears < 2.0) {
            return 'Infant (< 2 years)';
        } elseif ($ageYears >= 2.0 && $ageYears < 5.0) {
            return 'Toddler/Preschool (2-5 years)';
        } else {
            return 'School-age (≥ 5 years)';
        }
    }

    /**
     * Generate clinical note explaining the pediatric adjustment
     * 
     * @param float $ageYears Age in years
     * @param string $ageGroup Age group description
     * @param float $undercorrectionPercentage Undercorrection percentage applied
     * @param float $calculatedPower Original calculated IOL power
     * @param float $roundedPower Final rounded IOL power
     * @return string Clinical note
     */
    private function generateClinicalNote(float $ageYears, string $ageGroup, float $undercorrectionPercentage, float $calculatedPower, float $roundedPower): string
    {
        $note = "Pediatric IOL adjustment applied for {$ageGroup} patient (age: " . round($ageYears, 2) . " years). ";
        $note .= "An undercorrection of {$undercorrectionPercentage}% has been applied to account for expected myopic shift during ocular growth. ";
        $note .= "Original calculated power: {$calculatedPower} D. ";
        $note .= "Adjusted power: {$roundedPower} D (rounded to nearest 0.5 D step). ";
        $note .= "This adjustment helps prevent overcorrection and accommodates the natural refractive changes expected in pediatric patients.";
        
        return $note;
    }
}


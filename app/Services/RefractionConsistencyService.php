<?php

namespace App\Services;

/**
 * Refraction Consistency Checker Service
 * 
 * Compares auto-refraction results with subjective refraction results
 * to determine clinical consistency and flag significant discrepancies.
 * 
 * Clinical Logic:
 * - Sphere difference > 1.00 D → inconsistent
 * - Cylinder difference > 0.75 D → inconsistent
 */
class RefractionConsistencyService implements CalculatorInterface
{
    /**
     * Calculate refraction consistency between auto and subjective refractions
     * 
     * @param array $data Input data containing:
     *   - auto_sphere (float): Auto-refraction sphere in diopters (-20 to +20)
     *   - auto_cylinder (float): Auto-refraction cylinder in diopters (0 to -6)
     *   - auto_axis (float): Auto-refraction axis in degrees (0-180)
     *   - subjective_sphere (float): Subjective refraction sphere in diopters (-20 to +20)
     *   - subjective_cylinder (float): Subjective refraction cylinder in diopters (0 to -6)
     *   - subjective_axis (float): Subjective refraction axis in degrees (0-180)
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

        // Extract input values
        $autoSphere = (float)$data['auto_sphere'];
        $autoCylinder = (float)$data['auto_cylinder'];
        $autoAxis = (float)$data['auto_axis'];
        $subjectiveSphere = (float)$data['subjective_sphere'];
        $subjectiveCylinder = (float)$data['subjective_cylinder'];
        $subjectiveAxis = (float)$data['subjective_axis'];

        // Calculate absolute differences
        $deltaSphere = abs($autoSphere - $subjectiveSphere);
        $deltaCylinder = abs($autoCylinder - $subjectiveCylinder);

        // Apply clinical thresholds
        $isConsistent = true;
        $consistencyFlag = 'consistent';
        $clinicalMessage = 'Refraction results are clinically consistent. ';

        // Check sphere consistency threshold (> 1.00 D)
        if ($deltaSphere > 1.00) {
            $isConsistent = false;
            $consistencyFlag = 'inconsistent';
            $clinicalMessage = 'Significant sphere discrepancy detected. ';
            $clinicalMessage .= sprintf('Sphere difference of %.2f D exceeds the clinical threshold of 1.00 D. ', $deltaSphere);
            $clinicalMessage .= 'This may indicate measurement error, patient accommodation, or need for further refinement. ';
        }

        // Check cylinder consistency threshold (> 0.75 D)
        if ($deltaCylinder > 0.75) {
            $isConsistent = false;
            $consistencyFlag = 'inconsistent';
            if ($deltaSphere <= 1.00) {
                $clinicalMessage = 'Significant cylinder discrepancy detected. ';
            } else {
                $clinicalMessage .= 'Additionally, ';
            }
            $clinicalMessage .= sprintf('Cylinder difference of %.2f D exceeds the clinical threshold of 0.75 D. ', $deltaCylinder);
            $clinicalMessage .= 'Consider rechecking cylinder power and axis alignment. ';
        }

        // Add axis difference information if significant
        $axisDifference = abs($autoAxis - $subjectiveAxis);
        // Normalize axis difference to 0-90 degrees (since 180° = 0°)
        if ($axisDifference > 90) {
            $axisDifference = 180 - $axisDifference;
        }
        
        if ($axisDifference > 15 && ($deltaCylinder > 0.25)) {
            $clinicalMessage .= sprintf('Axis difference of %.1f° noted. ', $axisDifference);
        }

        // Finalize message
        if ($isConsistent) {
            $clinicalMessage .= 'Both measurements are within acceptable clinical limits.';
        } else {
            $clinicalMessage .= 'Recommend rechecking measurements and considering patient factors such as accommodation or measurement conditions.';
        }

        return [
            'success' => true,
            'delta_sphere' => round($deltaSphere, 2),
            'delta_cylinder' => round($deltaCylinder, 2),
            'delta_axis' => round($axisDifference, 1),
            'is_consistent' => $isConsistent,
            'consistency_flag' => $consistencyFlag,
            'clinical_message' => $clinicalMessage,
            'auto_refraction' => [
                'sphere' => round($autoSphere, 2),
                'cylinder' => round($autoCylinder, 2),
                'axis' => round($autoAxis, 1)
            ],
            'subjective_refraction' => [
                'sphere' => round($subjectiveSphere, 2),
                'cylinder' => round($subjectiveCylinder, 2),
                'axis' => round($subjectiveAxis, 1)
            ]
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

        // Validate auto-refraction sphere
        if (!isset($data['auto_sphere']) || $data['auto_sphere'] === null || $data['auto_sphere'] === '') {
            $errors[] = 'Auto-refraction sphere is required';
        } elseif (!is_numeric($data['auto_sphere'])) {
            $errors[] = 'Auto-refraction sphere must be numeric';
        } else {
            $autoSphere = (float)$data['auto_sphere'];
            if ($autoSphere < -20 || $autoSphere > 20) {
                $errors[] = 'Auto-refraction sphere must be within physiologic range (-20 to +20 D)';
            }
        }

        // Validate auto-refraction cylinder
        if (!isset($data['auto_cylinder']) || $data['auto_cylinder'] === null || $data['auto_cylinder'] === '') {
            $errors[] = 'Auto-refraction cylinder is required';
        } elseif (!is_numeric($data['auto_cylinder'])) {
            $errors[] = 'Auto-refraction cylinder must be numeric';
        } else {
            $autoCylinder = (float)$data['auto_cylinder'];
            if ($autoCylinder < -6 || $autoCylinder > 0) {
                $errors[] = 'Auto-refraction cylinder must be within physiologic range (0 to -6 D, negative cylinder notation)';
            }
        }

        // Validate auto-refraction axis
        if (!isset($data['auto_axis']) || $data['auto_axis'] === null || $data['auto_axis'] === '') {
            $errors[] = 'Auto-refraction axis is required';
        } elseif (!is_numeric($data['auto_axis'])) {
            $errors[] = 'Auto-refraction axis must be numeric';
        } else {
            $autoAxis = (float)$data['auto_axis'];
            if ($autoAxis < 0 || $autoAxis > 180) {
                $errors[] = 'Auto-refraction axis must be between 0 and 180 degrees';
            }
        }

        // Validate subjective refraction sphere
        if (!isset($data['subjective_sphere']) || $data['subjective_sphere'] === null || $data['subjective_sphere'] === '') {
            $errors[] = 'Subjective refraction sphere is required';
        } elseif (!is_numeric($data['subjective_sphere'])) {
            $errors[] = 'Subjective refraction sphere must be numeric';
        } else {
            $subjectiveSphere = (float)$data['subjective_sphere'];
            if ($subjectiveSphere < -20 || $subjectiveSphere > 20) {
                $errors[] = 'Subjective refraction sphere must be within physiologic range (-20 to +20 D)';
            }
        }

        // Validate subjective refraction cylinder
        if (!isset($data['subjective_cylinder']) || $data['subjective_cylinder'] === null || $data['subjective_cylinder'] === '') {
            $errors[] = 'Subjective refraction cylinder is required';
        } elseif (!is_numeric($data['subjective_cylinder'])) {
            $errors[] = 'Subjective refraction cylinder must be numeric';
        } else {
            $subjectiveCylinder = (float)$data['subjective_cylinder'];
            if ($subjectiveCylinder < -6 || $subjectiveCylinder > 0) {
                $errors[] = 'Subjective refraction cylinder must be within physiologic range (0 to -6 D, negative cylinder notation)';
            }
        }

        // Validate subjective refraction axis
        if (!isset($data['subjective_axis']) || $data['subjective_axis'] === null || $data['subjective_axis'] === '') {
            $errors[] = 'Subjective refraction axis is required';
        } elseif (!is_numeric($data['subjective_axis'])) {
            $errors[] = 'Subjective refraction axis must be numeric';
        } else {
            $subjectiveAxis = (float)$data['subjective_axis'];
            if ($subjectiveAxis < 0 || $subjectiveAxis > 180) {
                $errors[] = 'Subjective refraction axis must be between 0 and 180 degrees';
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
        return 'Refraction Consistency Checker';
    }
}


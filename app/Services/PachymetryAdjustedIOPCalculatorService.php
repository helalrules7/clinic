<?php

namespace App\Services;

/**
 * Pachymetry-Adjusted IOP Calculator Service
 * 
 * Corrects measured intraocular pressure (IOP) based on central corneal thickness (CCT)
 * using the Ehlers formula.
 * 
 * Clinical Logic:
 * - Ehlers Formula: Corrected IOP = Measured IOP + (CCT - 520) / 50
 * - CCT < 520 microns: IOP is underestimated, correction increases IOP
 * - CCT > 520 microns: IOP is overestimated, correction decreases IOP
 * - Standard CCT: 520-550 microns (average: 535 microns)
 * 
 * Clinical Significance:
 * - Thin corneas (<520 μm): May mask elevated IOP
 * - Thick corneas (>580 μm): May overestimate IOP
 * - Important for glaucoma diagnosis and management
 */
class PachymetryAdjustedIOPCalculatorService implements CalculatorInterface
{
    /**
     * Standard CCT reference value (microns)
     * Used in Ehlers formula
     */
    private const STANDARD_CCT = 520;

    /**
     * Correction factor (microns per mmHg)
     * Used in Ehlers formula: 50 microns = 1 mmHg
     */
    private const CORRECTION_FACTOR = 50;

    /**
     * Calculate corrected IOP based on CCT
     * 
     * @param array $data Input data containing:
     *   - measured_iop (float): Measured IOP in mmHg (5-60)
     *   - cct (float): Central corneal thickness in microns (400-700)
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

        $measuredIOP = (float)$data['measured_iop'];
        $cct = (float)$data['cct'];

        // Calculate correction using Ehlers formula
        // Corrected IOP = Measured IOP + (CCT - 520) / 50
        $correction = ($cct - self::STANDARD_CCT) / self::CORRECTION_FACTOR;
        $correctedIOP = $measuredIOP + $correction;
        $correctedIOP = round($correctedIOP, 1);

        // Determine correction direction
        $correctionDirection = 'none';
        if ($correction > 0.1) {
            $correctionDirection = 'increase';
        } elseif ($correction < -0.1) {
            $correctionDirection = 'decrease';
        }

        // Classify CCT
        $cctClassification = $this->classifyCCT($cct);

        // Generate clinical note
        $clinicalNote = $this->generateClinicalNote($measuredIOP, $correctedIOP, $cct, $correction, $correctionDirection, $cctClassification);

        return [
            'success' => true,
            'measured_iop' => round($measuredIOP, 1),
            'cct' => round($cct, 0),
            'cct_classification' => $cctClassification,
            'correction' => round($correction, 2),
            'correction_direction' => $correctionDirection,
            'corrected_iop' => $correctedIOP,
            'clinical_note' => $clinicalNote
        ];
    }

    /**
     * Classify CCT thickness
     * 
     * @param float $cct Central corneal thickness in microns
     * @return string Classification
     */
    private function classifyCCT(float $cct): string
    {
        if ($cct < 520) {
            return 'Thin';
        } elseif ($cct <= 580) {
            return 'Normal';
        } else {
            return 'Thick';
        }
    }

    /**
     * Generate clinical note
     * 
     * @param float $measuredIOP Measured IOP
     * @param float $correctedIOP Corrected IOP
     * @param float $cct CCT value
     * @param float $correction Correction amount
     * @param string $correctionDirection Correction direction
     * @param string $cctClassification CCT classification
     * @return string Clinical note
     */
    private function generateClinicalNote(float $measuredIOP, float $correctedIOP, float $cct, float $correction, string $correctionDirection, string $cctClassification): string
    {
        $note = sprintf("Measured IOP: %.1f mmHg. ", $measuredIOP);
        $note .= sprintf("CCT: %.0f microns (%s cornea). ", $cct, $cctClassification);
        
        if ($correctionDirection === 'increase') {
            $note .= sprintf("Pachymetry-adjusted IOP: %.1f mmHg (correction: +%.2f mmHg). ", $correctedIOP, abs($correction));
            $note .= "The thin cornea may have masked elevated IOP. ";
        } elseif ($correctionDirection === 'decrease') {
            $note .= sprintf("Pachymetry-adjusted IOP: %.1f mmHg (correction: %.2f mmHg). ", $correctedIOP, $correction);
            $note .= "The thick cornea may have overestimated IOP. ";
        } else {
            $note .= sprintf("Pachymetry-adjusted IOP: %.1f mmHg (minimal correction: %.2f mmHg). ", $correctedIOP, abs($correction));
            $note .= "CCT is within normal range. ";
        }
        
        $note .= "Ehlers formula applied for pachymetry correction.";
        
        return $note;
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

        // Validate measured IOP
        if (!isset($data['measured_iop']) || $data['measured_iop'] === null || $data['measured_iop'] === '') {
            $errors[] = 'Measured IOP is required';
        } else {
            if (!is_numeric($data['measured_iop'])) {
                $errors[] = 'Measured IOP must be numeric';
            } else {
                $iop = (float)$data['measured_iop'];
                if ($iop < 5 || $iop > 60) {
                    $errors[] = 'Measured IOP must be between 5 and 60 mmHg';
                }
            }
        }

        // Validate CCT
        if (!isset($data['cct']) || $data['cct'] === null || $data['cct'] === '') {
            $errors[] = 'Central corneal thickness (CCT) is required';
        } else {
            if (!is_numeric($data['cct'])) {
                $errors[] = 'CCT must be numeric';
            } else {
                $cct = (float)$data['cct'];
                if ($cct < 400 || $cct > 700) {
                    $errors[] = 'CCT must be between 400 and 700 microns';
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
        return 'Pachymetry-Adjusted IOP Calculator';
    }
}


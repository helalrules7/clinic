<?php

namespace App\Services;

/**
 * Post-Operative Outcome Analyzer Service
 * 
 * Analyzes post-operative outcomes after cataract surgery including
 * refractive accuracy and visual acuity improvement.
 * 
 * Clinical Logic:
 * - Calculate refractive error relative to pre-op target
 * - Classify refractive surprise using accepted thresholds (±0.5 D, ±1.0 D)
 * - Assess visual acuity improvement compared to pre-op baseline
 * - Generate outcome summary and surgical summary
 */
class PostOperativeOutcomeAnalyzerService implements SurgicalToolInterface
{
    /**
     * Analyze post-operative outcome
     * 
     * @param array $data Input data containing:
     *   - eye (string): Eye (OD or OS)
     *   - preop_bcva (string): Pre-operative BCVA (Snellen or LogMAR)
     *   - preop_target_sphere (float): Pre-operative target sphere (D)
     *   - preop_target_cylinder (float): Pre-operative target cylinder (D)
     *   - postop_sphere (float): Post-operative sphere (D)
     *   - postop_cylinder (float): Post-operative cylinder (D)
     *   - postop_bcva (string): Post-operative BCVA (Snellen or LogMAR)
     *   - surgery_date (string): Surgery date (Y-m-d format)
     * 
     * @return array Analysis results
     */
    public function analyze(array $data): array
    {
        $validation = $this->validate($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        $eye = strtoupper($data['eye']);
        $preopBcva = $data['preop_bcva'] ?? null;
        $preopTargetSphere = isset($data['preop_target_sphere']) ? (float)$data['preop_target_sphere'] : null;
        $preopTargetCylinder = isset($data['preop_target_cylinder']) ? (float)$data['preop_target_cylinder'] : null;
        $postopSphere = (float)$data['postop_sphere'];
        $postopCylinder = (float)$data['postop_cylinder'];
        $postopBcva = $data['postop_bcva'];
        $surgeryDate = $data['surgery_date'];

        // Calculate refractive errors
        $refractiveErrorSphere = null;
        $refractiveErrorCylinder = null;
        
        if ($preopTargetSphere !== null) {
            $refractiveErrorSphere = $postopSphere - $preopTargetSphere;
        }
        if ($preopTargetCylinder !== null) {
            $refractiveErrorCylinder = $postopCylinder - $preopTargetCylinder;
        }

        // Classify refractive outcome
        $refractiveOutcome = $this->classifyRefractiveOutcome($refractiveErrorSphere, $refractiveErrorCylinder);

        // Classify visual outcome
        $visualOutcome = null;
        $visualImprovement = null;
        if ($preopBcva) {
            $visualOutcome = $this->classifyVisualOutcome($preopBcva, $postopBcva);
            $visualImprovement = $this->calculateVisualImprovement($preopBcva, $postopBcva);
        }

        // Generate outcome summary
        $outcomeSummary = $this->generateOutcomeSummary($eye, $refractiveErrorSphere, $refractiveErrorCylinder, $refractiveOutcome, $visualOutcome, $visualImprovement);

        // Generate surgical summary
        $surgicalSummary = $this->generateSurgicalSummary($eye, $preopBcva, $postopBcva, $preopTargetSphere, $preopTargetCylinder, $postopSphere, $postopCylinder, $refractiveOutcome, $visualOutcome, $surgeryDate);

        return [
            'success' => true,
            'eye' => $eye,
            'preop_bcva' => $preopBcva,
            'preop_target_sphere' => $preopTargetSphere,
            'preop_target_cylinder' => $preopTargetCylinder,
            'postop_sphere' => $postopSphere,
            'postop_cylinder' => $postopCylinder,
            'postop_bcva' => $postopBcva,
            'surgery_date' => $surgeryDate,
            'refractive_error_sphere' => $refractiveErrorSphere,
            'refractive_error_cylinder' => $refractiveErrorCylinder,
            'refractive_outcome' => $refractiveOutcome,
            'visual_outcome' => $visualOutcome,
            'visual_improvement' => $visualImprovement,
            'outcome_summary' => $outcomeSummary,
            'surgical_summary' => $surgicalSummary
        ];
    }

    /**
     * Classify refractive outcome
     * 
     * @param float|null $sphereError Sphere error (D)
     * @param float|null $cylinderError Cylinder error (D)
     * @return string Refractive outcome classification
     */
    private function classifyRefractiveOutcome(?float $sphereError, ?float $cylinderError): string
    {
        // If no target refraction provided, cannot classify
        if ($sphereError === null && $cylinderError === null) {
            return 'Not assessed';
        }

        $absSphereError = $sphereError !== null ? abs($sphereError) : 0;
        $absCylinderError = $cylinderError !== null ? abs($cylinderError) : 0;

        // On target: |Sphere error| ≤ 0.5 D AND |Cylinder error| ≤ 0.5 D
        if ($absSphereError <= 0.5 && $absCylinderError <= 0.5) {
            return 'On target';
        }
        // Mild surprise: |Sphere error| ≤ 1.0 D AND |Cylinder error| ≤ 1.0 D (but not on target)
        elseif ($absSphereError <= 1.0 && $absCylinderError <= 1.0) {
            return 'Mild surprise';
        }
        // Significant surprise: |Sphere error| > 1.0 D OR |Cylinder error| > 1.0 D
        else {
            return 'Significant surprise';
        }
    }

    /**
     * Classify visual outcome
     * 
     * @param string $preopBcva Pre-operative BCVA
     * @param string $postopBcva Post-operative BCVA
     * @return string Visual outcome classification
     */
    private function classifyVisualOutcome(string $preopBcva, string $postopBcva): string
    {
        $preopLogmar = $this->convertToLogMAR($preopBcva);
        $postopLogmar = $this->convertToLogMAR($postopBcva);

        if ($preopLogmar === null || $postopLogmar === null) {
            return 'Not assessed';
        }

        $improvement = $preopLogmar - $postopLogmar; // Positive improvement means better vision (lower LogMAR)

        // Improved: Post-op BCVA better than pre-op BCVA (≥2 lines improvement = ≥0.2 LogMAR)
        if ($improvement >= 0.2) {
            return 'Improved';
        }
        // Unchanged: Post-op BCVA similar to pre-op BCVA (±1 line = ±0.1 LogMAR)
        elseif ($improvement >= -0.1 && $improvement < 0.2) {
            return 'Unchanged';
        }
        // Worse: Post-op BCVA worse than pre-op BCVA (≥2 lines worse = ≥0.2 LogMAR worse)
        else {
            return 'Worse';
        }
    }

    /**
     * Calculate visual improvement in lines
     * 
     * @param string $preopBcva Pre-operative BCVA
     * @param string $postopBcva Post-operative BCVA
     * @return float|null Visual improvement in lines (positive = improvement)
     */
    private function calculateVisualImprovement(string $preopBcva, string $postopBcva): ?float
    {
        $preopLogmar = $this->convertToLogMAR($preopBcva);
        $postopLogmar = $this->convertToLogMAR($postopBcva);

        if ($preopLogmar === null || $postopLogmar === null) {
            return null;
        }

        $improvementLogmar = $preopLogmar - $postopLogmar; // Positive = improvement
        // Convert LogMAR difference to lines (1 line = 0.1 LogMAR)
        return round($improvementLogmar / 0.1, 1);
    }

    /**
     * Convert BCVA to LogMAR
     * 
     * @param string $bcva BCVA value (Snellen or LogMAR format)
     * @return float|null LogMAR value or null if invalid
     */
    private function convertToLogMAR(string $bcva): ?float
    {
        $bcva = trim($bcva);
        
        // If already LogMAR format (numeric)
        if (is_numeric($bcva)) {
            $logmar = (float)$bcva;
            if ($logmar >= -0.3 && $logmar <= 3.0) {
                return $logmar;
            }
        }

        // Try Snellen format (e.g., "6/6", "6/12", "20/20")
        $bcva = str_replace(' ', '', $bcva);
        
        if (preg_match('/^(\d+(?:\.\d+)?)[\/\-](\d+(?:\.\d+)?)$/i', $bcva, $matches)) {
            $numerator = (float)$matches[1];
            $denominator = (float)$matches[2];
            
            if ($denominator > 0) {
                $snellenDecimal = $numerator / $denominator;
                if ($snellenDecimal > 0) {
                    return -log10($snellenDecimal);
                }
            }
        }

        return null;
    }

    /**
     * Generate outcome summary
     * 
     * @param string $eye Eye
     * @param float|null $sphereError Sphere error
     * @param float|null $cylinderError Cylinder error
     * @param string $refractiveOutcome Refractive outcome
     * @param string|null $visualOutcome Visual outcome
     * @param float|null $visualImprovement Visual improvement in lines
     * @return string Outcome summary
     */
    private function generateOutcomeSummary(string $eye, ?float $sphereError, ?float $cylinderError, string $refractiveOutcome, ?string $visualOutcome, ?float $visualImprovement): string
    {
        $summary = sprintf("Post-operative outcome analysis (%s): ", $eye);
        
        if ($refractiveOutcome !== 'Not assessed') {
            $summary .= sprintf("Refractive outcome: %s", $refractiveOutcome);
            if ($sphereError !== null) {
                $summary .= sprintf(" (Sphere error: %s%.2f D", $sphereError >= 0 ? '+' : '', $sphereError);
            }
            if ($cylinderError !== null) {
                $summary .= sprintf(", Cylinder error: %s%.2f D", $cylinderError >= 0 ? '+' : '', $cylinderError);
            }
            $summary .= "). ";
        }
        
        if ($visualOutcome && $visualOutcome !== 'Not assessed') {
            $summary .= sprintf("Visual outcome: %s", $visualOutcome);
            if ($visualImprovement !== null) {
                $summary .= sprintf(" (%s%.1f lines)", $visualImprovement >= 0 ? '+' : '', $visualImprovement);
            }
            $summary .= ". ";
        }
        
        return trim($summary);
    }

    /**
     * Generate surgical summary for operative report
     * 
     * @param string $eye Eye
     * @param string|null $preopBcva Pre-op BCVA
     * @param string $postopBcva Post-op BCVA
     * @param float|null $preopTargetSphere Pre-op target sphere
     * @param float|null $preopTargetCylinder Pre-op target cylinder
     * @param float $postopSphere Post-op sphere
     * @param float $postopCylinder Post-op cylinder
     * @param string $refractiveOutcome Refractive outcome
     * @param string|null $visualOutcome Visual outcome
     * @param string $surgeryDate Surgery date
     * @return string Surgical summary
     */
    private function generateSurgicalSummary(string $eye, ?string $preopBcva, string $postopBcva, ?float $preopTargetSphere, ?float $preopTargetCylinder, float $postopSphere, float $postopCylinder, string $refractiveOutcome, ?string $visualOutcome, string $surgeryDate): string
    {
        $summary = sprintf("Cataract surgery performed on %s (%s) on %s. ", $eye, date('F j, Y', strtotime($surgeryDate)), date('F j, Y', strtotime($surgeryDate)));
        
        if ($preopBcva) {
            $summary .= sprintf("Pre-operative BCVA: %s. ", $preopBcva);
        }
        $summary .= sprintf("Post-operative BCVA: %s. ", $postopBcva);
        
        if ($preopTargetSphere !== null || $preopTargetCylinder !== null) {
            $summary .= "Target refraction: ";
            $targetParts = [];
            if ($preopTargetSphere !== null) {
                $targetParts[] = sprintf("%.2f D", $preopTargetSphere);
            }
            if ($preopTargetCylinder !== null) {
                $targetParts[] = sprintf("%.2f D", $preopTargetCylinder);
            }
            $summary .= implode(" / ", $targetParts) . ". ";
        }
        
        $summary .= sprintf("Achieved refraction: %.2f D / %.2f D. ", $postopSphere, $postopCylinder);
        $summary .= sprintf("Refractive outcome: %s. ", $refractiveOutcome);
        
        if ($visualOutcome && $visualOutcome !== 'Not assessed') {
            $summary .= sprintf("Visual outcome: %s. ", $visualOutcome);
        }
        
        $summary .= "Patient advised regarding post-operative care and follow-up schedule.";
        
        return $summary;
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

        // Validate eye
        if (!isset($data['eye']) || empty($data['eye'])) {
            $errors[] = 'Eye (OD/OS) is required';
        } else {
            $eye = strtoupper(trim($data['eye']));
            if (!in_array($eye, ['OD', 'OS'])) {
                $errors[] = 'Eye must be OD or OS';
            }
        }

        // Validate post-op sphere
        if (!isset($data['postop_sphere']) || $data['postop_sphere'] === null || $data['postop_sphere'] === '') {
            $errors[] = 'Post-operative sphere is required';
        } else {
            if (!is_numeric($data['postop_sphere'])) {
                $errors[] = 'Post-operative sphere must be numeric';
            } else {
                $sphere = (float)$data['postop_sphere'];
                if ($sphere < -20.0 || $sphere > 20.0) {
                    $errors[] = 'Post-operative sphere must be between -20.0 and 20.0 D';
                }
            }
        }

        // Validate post-op cylinder
        if (!isset($data['postop_cylinder']) || $data['postop_cylinder'] === null || $data['postop_cylinder'] === '') {
            $errors[] = 'Post-operative cylinder is required';
        } else {
            if (!is_numeric($data['postop_cylinder'])) {
                $errors[] = 'Post-operative cylinder must be numeric';
            } else {
                $cylinder = (float)$data['postop_cylinder'];
                if ($cylinder < -10.0 || $cylinder > 10.0) {
                    $errors[] = 'Post-operative cylinder must be between -10.0 and 10.0 D';
                }
            }
        }

        // Validate post-op BCVA
        if (!isset($data['postop_bcva']) || empty($data['postop_bcva'])) {
            $errors[] = 'Post-operative BCVA is required';
        } else {
            $bcva = trim($data['postop_bcva']);
            if (!is_numeric($bcva) && !preg_match('/^\d+(?:\.\d+)?[\/\-]\d+(?:\.\d+)?$/i', str_replace(' ', '', $bcva))) {
                $errors[] = 'Post-operative BCVA format is invalid. Use Snellen (e.g., 6/12) or LogMAR (e.g., 0.3) format';
            }
        }

        // Validate pre-op target sphere (optional)
        if (isset($data['preop_target_sphere']) && $data['preop_target_sphere'] !== null && $data['preop_target_sphere'] !== '') {
            if (!is_numeric($data['preop_target_sphere'])) {
                $errors[] = 'Pre-operative target sphere must be numeric';
            } else {
                $sphere = (float)$data['preop_target_sphere'];
                if ($sphere < -20.0 || $sphere > 20.0) {
                    $errors[] = 'Pre-operative target sphere must be between -20.0 and 20.0 D';
                }
            }
        }

        // Validate pre-op target cylinder (optional)
        if (isset($data['preop_target_cylinder']) && $data['preop_target_cylinder'] !== null && $data['preop_target_cylinder'] !== '') {
            if (!is_numeric($data['preop_target_cylinder'])) {
                $errors[] = 'Pre-operative target cylinder must be numeric';
            } else {
                $cylinder = (float)$data['preop_target_cylinder'];
                if ($cylinder < -10.0 || $cylinder > 10.0) {
                    $errors[] = 'Pre-operative target cylinder must be between -10.0 and 10.0 D';
                }
            }
        }

        // Validate pre-op BCVA (optional but recommended)
        if (isset($data['preop_bcva']) && !empty($data['preop_bcva'])) {
            $bcva = trim($data['preop_bcva']);
            if (!is_numeric($bcva) && !preg_match('/^\d+(?:\.\d+)?[\/\-]\d+(?:\.\d+)?$/i', str_replace(' ', '', $bcva))) {
                $errors[] = 'Pre-operative BCVA format is invalid. Use Snellen (e.g., 6/12) or LogMAR (e.g., 0.3) format';
            }
        }

        // Validate surgery date
        if (!isset($data['surgery_date']) || empty($data['surgery_date'])) {
            $errors[] = 'Surgery date is required';
        } else {
            $date = strtotime($data['surgery_date']);
            if ($date === false) {
                $errors[] = 'Invalid surgery date format. Use Y-m-d format (e.g., 2024-01-15)';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get tool name
     * 
     * @return string Tool name
     */
    public function getToolName(): string
    {
        return 'Post-Operative Outcome Analyzer';
    }
}


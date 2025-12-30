<?php

namespace App\Services;

/**
 * Cataract Surgery Readiness Score Service
 * 
 * Calculates cataract surgery readiness based on BCVA, visual complaints,
 * lens opacity grade, and complications. Provides surgical recommendation.
 * 
 * Clinical Logic:
 * - Weighted scoring system combining objective and subjective factors
 * - Higher weight to reduced BCVA, significant complaints, advanced opacity
 * - Classification: Not ready, Consider surgery, Surgery recommended, Urgent surgery
 */
class CataractSurgeryReadinessService implements SurgicalToolInterface
{
    /**
     * Analyze cataract surgery readiness
     * 
     * @param array $data Input data containing:
     *   - bcva_od (string): Best corrected visual acuity OD (Snellen or LogMAR format)
     *   - bcva_os (string): Best corrected visual acuity OS (Snellen or LogMAR format)
     *   - visual_complaints_score (int): Patient-reported visual complaints score (0-10)
     *   - lens_opacity_grade (string): Lens opacity grade (Grade 1-4 or LOCS III)
     *   - complications (array): Array of complications (e.g., ['phacomorphic', 'psc', 'other'])
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

        $bcvaOd = $data['bcva_od'] ?? null;
        $bcvaOs = $data['bcva_os'] ?? null;
        $visualComplaintsScore = (int)$data['visual_complaints_score'];
        $lensOpacityGrade = $data['lens_opacity_grade'];
        $complications = $data['complications'] ?? [];

        // Calculate scores for each factor
        $bcvaScoreOd = $bcvaOd ? $this->scoreBCVA($bcvaOd) : 0;
        $bcvaScoreOs = $bcvaOs ? $this->scoreBCVA($bcvaOs) : 0;
        // Use the worse eye for scoring
        $bcvaScore = max($bcvaScoreOd, $bcvaScoreOs);
        
        $visualComplaintsScore = $this->scoreVisualComplaints($visualComplaintsScore);
        $lensOpacityScore = $this->scoreLensOpacity($lensOpacityGrade);
        $complicationsScore = $this->scoreComplications($complications);

        // Calculate total readiness score
        $totalScore = $bcvaScore + $visualComplaintsScore + $lensOpacityScore + $complicationsScore;

        // Determine readiness classification
        $classification = $this->classifyReadiness($totalScore);
        $recommendation = $this->getRecommendation($classification);

        // Generate clinical summary
        $clinicalSummary = $this->generateClinicalSummary($bcvaOd, $bcvaOs, $visualComplaintsScore, $lensOpacityGrade, $complications, $totalScore, $classification, $recommendation);

        return [
            'success' => true,
            'bcva_od' => $bcvaOd,
            'bcva_os' => $bcvaOs,
            'visual_complaints_score' => $visualComplaintsScore,
            'lens_opacity_grade' => $lensOpacityGrade,
            'complications' => $complications,
            'bcva_score' => $bcvaScore,
            'visual_complaints_score_points' => $visualComplaintsScore,
            'lens_opacity_score' => $lensOpacityScore,
            'complications_score' => $complicationsScore,
            'total_score' => $totalScore,
            'readiness_classification' => $classification,
            'recommendation' => $recommendation,
            'clinical_summary' => $clinicalSummary
        ];
    }

    /**
     * Score BCVA
     * 
     * @param string $bcva BCVA value (Snellen or LogMAR format)
     * @return int Score (0-3)
     */
    private function scoreBCVA(string $bcva): int
    {
        // Convert to LogMAR for consistent scoring
        $logmar = $this->convertToLogMAR($bcva);
        
        if ($logmar === null) {
            return 0; // Invalid BCVA, default to 0
        }

        // BCVA ≥ 6/12 (0.3 LogMAR): 0 points
        if ($logmar <= 0.3) {
            return 0;
        }
        // BCVA 6/15-6/18 (0.4-0.5 LogMAR): 1 point
        elseif ($logmar <= 0.5) {
            return 1;
        }
        // BCVA 6/24-6/36 (0.6-0.8 LogMAR): 2 points
        elseif ($logmar <= 0.8) {
            return 2;
        }
        // BCVA ≤ 6/60 (≥1.0 LogMAR): 3 points
        else {
            return 3;
        }
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
        // Remove spaces
        $bcva = str_replace(' ', '', $bcva);
        
        // Pattern: number/number or number-number
        if (preg_match('/^(\d+(?:\.\d+)?)[\/\-](\d+(?:\.\d+)?)$/i', $bcva, $matches)) {
            $numerator = (float)$matches[1];
            $denominator = (float)$matches[2];
            
            if ($denominator > 0) {
                $snellenDecimal = $numerator / $denominator;
                // Convert Snellen to LogMAR: LogMAR = -log10(Snellen decimal)
                if ($snellenDecimal > 0) {
                    return -log10($snellenDecimal);
                }
            }
        }

        return null; // Invalid format
    }

    /**
     * Score visual complaints
     * 
     * @param int $score Visual complaints score (0-10)
     * @return int Score points (0-3)
     */
    private function scoreVisualComplaints(int $score): int
    {
        if ($score <= 3) {
            return 0;
        } elseif ($score <= 6) {
            return 1;
        } elseif ($score <= 8) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Score lens opacity grade
     * 
     * @param string $grade Lens opacity grade
     * @return int Score (0-3)
     */
    private function scoreLensOpacity(string $grade): int
    {
        $grade = strtolower(trim($grade));
        
        // Grade 1 (Mild): 0 points
        if (strpos($grade, 'grade 1') !== false || strpos($grade, 'mild') !== false || strpos($grade, '1') !== false) {
            return 0;
        }
        // Grade 2 (Moderate): 1 point
        elseif (strpos($grade, 'grade 2') !== false || strpos($grade, 'moderate') !== false || strpos($grade, '2') !== false) {
            return 1;
        }
        // Grade 3 (Advanced): 2 points
        elseif (strpos($grade, 'grade 3') !== false || strpos($grade, 'advanced') !== false || strpos($grade, '3') !== false) {
            return 2;
        }
        // Grade 4 (Severe): 3 points
        elseif (strpos($grade, 'grade 4') !== false || strpos($grade, 'severe') !== false || strpos($grade, '4') !== false) {
            return 3;
        }
        else {
            // Default to moderate if unclear
            return 1;
        }
    }

    /**
     * Score complications
     * 
     * @param array $complications Array of complications
     * @return int Score points
     */
    private function scoreComplications(array $complications): int
    {
        $score = 0;
        
        foreach ($complications as $complication) {
            $comp = strtolower(trim($complication));
            
            // Phacomorphic risk: +2 points
            if (strpos($comp, 'phacomorphic') !== false) {
                $score += 2;
            }
            // PSC affecting vision: +1 point
            elseif (strpos($comp, 'psc') !== false || strpos($comp, 'posterior subcapsular') !== false) {
                $score += 1;
            }
            // Other complications: +1 point each
            else {
                $score += 1;
            }
        }
        
        return $score;
    }

    /**
     * Classify readiness based on total score
     * 
     * @param int $totalScore Total readiness score
     * @return string Classification
     */
    private function classifyReadiness(int $totalScore): string
    {
        if ($totalScore <= 3) {
            return 'Not ready';
        } elseif ($totalScore <= 6) {
            return 'Consider surgery';
        } elseif ($totalScore <= 10) {
            return 'Surgery recommended';
        } else {
            return 'Urgent surgery';
        }
    }

    /**
     * Get recommendation based on classification
     * 
     * @param string $classification Readiness classification
     * @return string Recommendation
     */
    private function getRecommendation(string $classification): string
    {
        switch ($classification) {
            case 'Not ready':
                return 'Continue monitoring';
            case 'Consider surgery':
                return 'Discuss with patient';
            case 'Surgery recommended':
                return 'Schedule surgery';
            case 'Urgent surgery':
                return 'Expedite evaluation';
            default:
                return 'Continue monitoring';
        }
    }

    /**
     * Generate clinical summary
     * 
     * @param string|null $bcvaOd BCVA OD
     * @param string|null $bcvaOs BCVA OS
     * @param int $visualComplaintsScore Visual complaints score
     * @param string $lensOpacityGrade Lens opacity grade
     * @param array $complications Complications
     * @param int $totalScore Total score
     * @param string $classification Classification
     * @param string $recommendation Recommendation
     * @return string Clinical summary
     */
    private function generateClinicalSummary(?string $bcvaOd, ?string $bcvaOs, int $visualComplaintsScore, string $lensOpacityGrade, array $complications, int $totalScore, string $classification, string $recommendation): string
    {
        $summary = sprintf("Cataract surgery readiness assessment: %s (Score: %d). ", $classification, $totalScore);
        
        $bcvaParts = [];
        if ($bcvaOd) {
            $bcvaParts[] = "OD: " . $bcvaOd;
        }
        if ($bcvaOs) {
            $bcvaParts[] = "OS: " . $bcvaOs;
        }
        if (!empty($bcvaParts)) {
            $summary .= "BCVA: " . implode(", ", $bcvaParts) . ". ";
        }
        
        $summary .= sprintf("Visual complaints score: %d/10. ", $visualComplaintsScore);
        $summary .= sprintf("Lens opacity: %s. ", $lensOpacityGrade);
        
        if (!empty($complications)) {
            $summary .= "Complications: " . implode(", ", $complications) . ". ";
        }
        
        $summary .= sprintf("Recommendation: %s.", $recommendation);
        
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

        // At least one BCVA is required
        if (empty($data['bcva_od']) && empty($data['bcva_os'])) {
            $errors[] = 'At least one BCVA (OD or OS) is required';
        }

        // Validate BCVA OD if provided
        if (!empty($data['bcva_od'])) {
            $bcvaOd = trim($data['bcva_od']);
            // Basic validation - should be Snellen or LogMAR format
            if (!is_numeric($bcvaOd) && !preg_match('/^\d+(?:\.\d+)?[\/\-]\d+(?:\.\d+)?$/i', str_replace(' ', '', $bcvaOd))) {
                $errors[] = 'BCVA OD format is invalid. Use Snellen (e.g., 6/12) or LogMAR (e.g., 0.3) format';
            }
        }

        // Validate BCVA OS if provided
        if (!empty($data['bcva_os'])) {
            $bcvaOs = trim($data['bcva_os']);
            // Basic validation - should be Snellen or LogMAR format
            if (!is_numeric($bcvaOs) && !preg_match('/^\d+(?:\.\d+)?[\/\-]\d+(?:\.\d+)?$/i', str_replace(' ', '', $bcvaOs))) {
                $errors[] = 'BCVA OS format is invalid. Use Snellen (e.g., 6/12) or LogMAR (e.g., 0.3) format';
            }
        }

        // Validate visual complaints score
        if (!isset($data['visual_complaints_score']) || $data['visual_complaints_score'] === null || $data['visual_complaints_score'] === '') {
            $errors[] = 'Visual complaints score is required';
        } else {
            if (!is_numeric($data['visual_complaints_score'])) {
                $errors[] = 'Visual complaints score must be numeric';
            } else {
                $score = (int)$data['visual_complaints_score'];
                if ($score < 0 || $score > 10) {
                    $errors[] = 'Visual complaints score must be between 0 and 10';
                }
            }
        }

        // Validate lens opacity grade
        if (!isset($data['lens_opacity_grade']) || empty($data['lens_opacity_grade'])) {
            $errors[] = 'Lens opacity grade is required';
        }

        // Validate complications (optional but should be array if provided)
        if (isset($data['complications']) && !is_array($data['complications'])) {
            $errors[] = 'Complications must be an array';
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
        return 'Cataract Surgery Readiness Score';
    }
}


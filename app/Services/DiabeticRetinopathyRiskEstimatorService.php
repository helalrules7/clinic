<?php

namespace App\Services;

/**
 * Diabetic Retinopathy Risk Estimator Service
 * 
 * Estimates diabetic retinopathy risk based on duration of diabetes,
 * glycemic control (HbA1c), blood pressure, and fundus examination grade.
 * Provides follow-up interval recommendations.
 * 
 * Clinical Logic:
 * - Rule-based scoring system combining all risk factors
 * - Risk levels: Low, Moderate, High, Very High
 * - Follow-up intervals: 12 months (Low), 6 months (Moderate), 3 months (High), 1-2 months (Very High)
 */
class DiabeticRetinopathyRiskEstimatorService implements AnalyzerInterface
{
    /**
     * Analyze diabetic retinopathy risk
     * 
     * @param array $data Input data containing:
     *   - duration_years (float): Duration of diabetes in years
     *   - hba1c (float): Latest HbA1c value (%)
     *   - systolic_bp (int): Systolic blood pressure (mmHg)
     *   - diastolic_bp (int): Diastolic blood pressure (mmHg)
     *   - fundus_grade (string): Fundus examination grade (No DR, Mild NPDR, Moderate NPDR, Severe NPDR, PDR)
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

        $durationYears = (float)$data['duration_years'];
        $hba1c = (float)$data['hba1c'];
        $systolicBP = (int)$data['systolic_bp'];
        $diastolicBP = (int)$data['diastolic_bp'];
        $fundusGrade = $data['fundus_grade'];

        // Calculate scores for each factor
        $durationScore = $this->scoreDuration($durationYears);
        $hba1cScore = $this->scoreHbA1c($hba1c);
        $bpScore = $this->scoreBloodPressure($systolicBP, $diastolicBP);
        $fundusScore = $this->scoreFundusGrade($fundusGrade);

        // Calculate total risk score
        $totalScore = $durationScore + $hba1cScore + $bpScore + $fundusScore;

        // Determine risk level and follow-up interval
        $riskLevel = $this->classifyRisk($totalScore);
        $followUpInterval = $this->getFollowUpInterval($totalScore);

        // Identify contributing factors
        $contributingFactors = $this->identifyContributingFactors($durationScore, $hba1cScore, $bpScore, $fundusScore);

        // Generate clinical summary
        $clinicalSummary = $this->generateClinicalSummary($durationYears, $hba1c, $systolicBP, $diastolicBP, $fundusGrade, $riskLevel, $followUpInterval, $contributingFactors);

        return [
            'success' => true,
            'duration_years' => round($durationYears, 1),
            'hba1c' => round($hba1c, 1),
            'systolic_bp' => $systolicBP,
            'diastolic_bp' => $diastolicBP,
            'fundus_grade' => $fundusGrade,
            'duration_score' => $durationScore,
            'hba1c_score' => $hba1cScore,
            'bp_score' => $bpScore,
            'fundus_score' => $fundusScore,
            'total_score' => $totalScore,
            'risk_level' => $riskLevel,
            'follow_up_interval' => $followUpInterval,
            'contributing_factors' => $contributingFactors,
            'clinical_summary' => $clinicalSummary
        ];
    }

    /**
     * Score duration of diabetes
     * 
     * @param float $years Duration in years
     * @return int Score (0-3)
     */
    private function scoreDuration(float $years): int
    {
        if ($years < 5) {
            return 0;
        } elseif ($years < 10) {
            return 1;
        } elseif ($years < 15) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Score HbA1c value
     * 
     * @param float $hba1c HbA1c percentage
     * @return int Score (0-3)
     */
    private function scoreHbA1c(float $hba1c): int
    {
        if ($hba1c < 7.0) {
            return 0;
        } elseif ($hba1c < 8.0) {
            return 1;
        } elseif ($hba1c < 9.0) {
            return 2;
        } else {
            return 3;
        }
    }

    /**
     * Score blood pressure
     * 
     * @param int $systolic Systolic BP (mmHg)
     * @param int $diastolic Diastolic BP (mmHg)
     * @return int Score (0-3)
     */
    private function scoreBloodPressure(int $systolic, int $diastolic): int
    {
        // Normal: <130/80
        if ($systolic < 130 && $diastolic < 80) {
            return 0;
        }
        // Elevated: 130-139/80-89
        elseif (($systolic >= 130 && $systolic < 140) && ($diastolic >= 80 && $diastolic < 90)) {
            return 1;
        }
        // Stage 1: 140-159/90-99
        elseif (($systolic >= 140 && $systolic < 160) && ($diastolic >= 90 && $diastolic < 100)) {
            return 2;
        }
        // Stage 2+: ≥160/≥100
        else {
            return 3;
        }
    }

    /**
     * Score fundus examination grade
     * 
     * @param string $grade Fundus grade
     * @return int Score (0-4)
     */
    private function scoreFundusGrade(string $grade): int
    {
        $grade = strtolower(trim($grade));
        
        if (strpos($grade, 'no dr') !== false || strpos($grade, 'normal') !== false) {
            return 0;
        } elseif (strpos($grade, 'mild npdr') !== false || strpos($grade, 'mild') !== false) {
            return 1;
        } elseif (strpos($grade, 'moderate npdr') !== false || strpos($grade, 'moderate') !== false) {
            return 2;
        } elseif (strpos($grade, 'severe npdr') !== false || strpos($grade, 'severe') !== false) {
            return 3;
        } elseif (strpos($grade, 'pdr') !== false || strpos($grade, 'proliferative') !== false) {
            return 4;
        } else {
            // Default to moderate if unclear
            return 2;
        }
    }

    /**
     * Classify risk level based on total score
     * 
     * @param int $totalScore Total risk score
     * @return string Risk level
     */
    private function classifyRisk(int $totalScore): string
    {
        if ($totalScore <= 3) {
            return 'Low';
        } elseif ($totalScore <= 6) {
            return 'Moderate';
        } elseif ($totalScore <= 10) {
            return 'High';
        } else {
            return 'Very High';
        }
    }

    /**
     * Get recommended follow-up interval
     * 
     * @param int $totalScore Total risk score
     * @return string Follow-up interval
     */
    private function getFollowUpInterval(int $totalScore): string
    {
        if ($totalScore <= 3) {
            return '12 months';
        } elseif ($totalScore <= 6) {
            return '6 months';
        } elseif ($totalScore <= 10) {
            return '3 months';
        } else {
            return '1-2 months';
        }
    }

    /**
     * Identify contributing factors to risk
     * 
     * @param int $durationScore Duration score
     * @param int $hba1cScore HbA1c score
     * @param int $bpScore BP score
     * @param int $fundusScore Fundus score
     * @return array Contributing factors
     */
    private function identifyContributingFactors(int $durationScore, int $hba1cScore, int $bpScore, int $fundusScore): array
    {
        $factors = [];
        
        if ($durationScore >= 2) {
            $factors[] = 'Long duration of diabetes';
        }
        if ($hba1cScore >= 2) {
            $factors[] = 'Poor glycemic control';
        }
        if ($bpScore >= 2) {
            $factors[] = 'Uncontrolled blood pressure';
        }
        if ($fundusScore >= 2) {
            $factors[] = 'Advanced fundus changes';
        }
        
        return $factors;
    }

    /**
     * Generate clinical summary
     * 
     * @param float $durationYears Duration of diabetes
     * @param float $hba1c HbA1c value
     * @param int $systolicBP Systolic BP
     * @param int $diastolicBP Diastolic BP
     * @param string $fundusGrade Fundus grade
     * @param string $riskLevel Risk level
     * @param string $followUpInterval Follow-up interval
     * @param array $contributingFactors Contributing factors
     * @return string Clinical summary
     */
    private function generateClinicalSummary(float $durationYears, float $hba1c, int $systolicBP, int $diastolicBP, string $fundusGrade, string $riskLevel, string $followUpInterval, array $contributingFactors): string
    {
        $summary = sprintf("Diabetic retinopathy risk assessment: %s risk. ", $riskLevel);
        $summary .= sprintf("Patient with diabetes duration %.1f years, HbA1c %.1f%%, BP %d/%d mmHg, fundus examination: %s. ", 
            $durationYears, $hba1c, $systolicBP, $diastolicBP, $fundusGrade);
        
        if (!empty($contributingFactors)) {
            $summary .= "Key contributing factors: " . implode(', ', $contributingFactors) . ". ";
        }
        
        $summary .= sprintf("Recommended follow-up interval: %s. ", $followUpInterval);
        $summary .= "Consider optimizing glycemic control and blood pressure management to reduce retinopathy progression risk.";
        
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

        // Validate duration of diabetes
        if (!isset($data['duration_years']) || $data['duration_years'] === null || $data['duration_years'] === '') {
            $errors[] = 'Duration of diabetes is required';
        } else {
            if (!is_numeric($data['duration_years'])) {
                $errors[] = 'Duration of diabetes must be numeric';
            } else {
                $duration = (float)$data['duration_years'];
                if ($duration < 0 || $duration > 100) {
                    $errors[] = 'Duration of diabetes must be between 0 and 100 years';
                }
            }
        }

        // Validate HbA1c
        if (!isset($data['hba1c']) || $data['hba1c'] === null || $data['hba1c'] === '') {
            $errors[] = 'HbA1c value is required';
        } else {
            if (!is_numeric($data['hba1c'])) {
                $errors[] = 'HbA1c must be numeric';
            } else {
                $hba1c = (float)$data['hba1c'];
                if ($hba1c < 3.0 || $hba1c > 20.0) {
                    $errors[] = 'HbA1c must be between 3.0% and 20.0%';
                }
            }
        }

        // Validate blood pressure
        if (!isset($data['systolic_bp']) || $data['systolic_bp'] === null || $data['systolic_bp'] === '') {
            $errors[] = 'Systolic blood pressure is required';
        } else {
            if (!is_numeric($data['systolic_bp'])) {
                $errors[] = 'Systolic blood pressure must be numeric';
            } else {
                $systolic = (int)$data['systolic_bp'];
                if ($systolic < 50 || $systolic > 250) {
                    $errors[] = 'Systolic blood pressure must be between 50 and 250 mmHg';
                }
            }
        }

        if (!isset($data['diastolic_bp']) || $data['diastolic_bp'] === null || $data['diastolic_bp'] === '') {
            $errors[] = 'Diastolic blood pressure is required';
        } else {
            if (!is_numeric($data['diastolic_bp'])) {
                $errors[] = 'Diastolic blood pressure must be numeric';
            } else {
                $diastolic = (int)$data['diastolic_bp'];
                if ($diastolic < 30 || $diastolic > 150) {
                    $errors[] = 'Diastolic blood pressure must be between 30 and 150 mmHg';
                }
            }
        }

        // Validate fundus grade
        if (!isset($data['fundus_grade']) || empty($data['fundus_grade'])) {
            $errors[] = 'Fundus examination grade is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Get analyzer name
     * 
     * @return string Analyzer name
     */
    public function getAnalyzerName(): string
    {
        return 'Diabetic Retinopathy Risk Estimator';
    }
}


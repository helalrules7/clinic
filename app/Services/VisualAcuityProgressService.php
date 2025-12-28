<?php

namespace App\Services;

/**
 * Visual Acuity Progress Calculator Service
 * 
 * Calculates visual acuity progress over time, supporting both Snellen and LogMAR formats.
 * Performs calculations per eye independently (OD/OS).
 * 
 * Clinical Logic:
 * - Normalizes all VA values to LogMAR internally
 * - Calculates percentage change and trend (improving/worsening/stable)
 * - Improvement: LogMAR decrease ≥ 0.1 (≥1 line improvement)
 * - Worsening: LogMAR increase ≥ 0.1
 * - Stable: change < 0.1 LogMAR
 */
class VisualAcuityProgressService implements CalculatorInterface
{
    /**
     * Calculate visual acuity progress over time
     * 
     * @param array $data Input data containing:
     *   - visits (array): Array of visit data, each containing:
     *     - eye (string): 'OD' or 'OS'
     *     - va_value (string|float): Visual acuity (Snellen format like "6/6" or LogMAR value)
     *     - va_format (string): 'snellen' or 'logmar'
     *     - date (string): Measurement date (Y-m-d format)
     * 
     * @return array Calculation results
     */
    public function calculate(array $data): array
    {
        try {
            $validation = $this->validate($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'errors' => $validation['errors']
                ];
            }

            $visits = $data['visits'];
            
            // Sort visits by date
            usort($visits, function($a, $b) {
                $dateA = strtotime($a['date']);
                $dateB = strtotime($b['date']);
                if ($dateA === false || $dateB === false) {
                    return 0;
                }
                return $dateA <=> $dateB;
            });

            // Separate visits by eye
            $odVisits = [];
            $osVisits = [];

            foreach ($visits as $index => $visit) {
                try {
                    $eye = strtoupper($visit['eye'] ?? '');
                    $vaFormat = strtolower($visit['va_format'] ?? '');
                    $vaValue = $visit['va_value'] ?? '';
                    
                    if (empty($eye) || empty($vaFormat) || empty($vaValue)) {
                        error_log("Visual Acuity Progress - Skipping visit $index: missing required fields");
                        continue;
                    }
                    
                    // Convert to LogMAR
                    $logmar = $this->convertToLogMAR($vaValue, $vaFormat);
                    
                    if ($logmar === null) {
                        error_log("Visual Acuity Progress - Skipping visit $index: invalid VA value '$vaValue' for format '$vaFormat'");
                        continue; // Skip invalid entries
                    }

                    $visitData = [
                        'date' => $visit['date'],
                        'va_value' => $vaValue,
                        'va_format' => $vaFormat,
                        'va_logmar' => $logmar,
                        'va_snellen' => $this->logmarToSnellen($logmar)
                    ];

                    if ($eye === 'OD') {
                        $odVisits[] = $visitData;
                    } elseif ($eye === 'OS') {
                        $osVisits[] = $visitData;
                    }
                } catch (\Exception $e) {
                    error_log("Visual Acuity Progress - Error processing visit $index: " . $e->getMessage());
                    continue;
                }
            }

            // Calculate progress for each eye
            $odResults = $this->calculateEyeProgress($odVisits, 'OD');
            $osResults = $this->calculateEyeProgress($osVisits, 'OS');

            // Determine overall trend
            $overallTrend = $this->determineOverallTrend($odResults, $osResults);
            $summaryNote = $this->generateSummaryNote($odResults, $osResults, $overallTrend);

            return [
                'success' => true,
                'od' => $odResults,
                'os' => $osResults,
                'overall_trend' => $overallTrend,
                'summary_note' => $summaryNote
            ];
        } catch (\Exception $e) {
            error_log("Visual Acuity Progress Service calculate() error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [
                'success' => false,
                'error' => 'Calculation error: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Calculate progress for a single eye
     * 
     * @param array $visits Array of visit data for the eye
     * @param string $eye Eye identifier ('OD' or 'OS')
     * @return array Eye progress results
     */
    private function calculateEyeProgress(array $visits, string $eye): array
    {
        if (count($visits) < 2) {
            return [
                'visits' => $visits,
                'initial_va_logmar' => null,
                'final_va_logmar' => null,
                'initial_va_snellen' => null,
                'final_va_snellen' => null,
                'logmar_change' => null,
                'percentage_change' => null,
                'trend' => 'insufficient_data',
                'clinical_note' => 'Insufficient data: At least 2 visits required for progress calculation.'
            ];
        }

        $initialVisit = $visits[0];
        $finalVisit = $visits[count($visits) - 1];

        $initialLogMAR = $initialVisit['va_logmar'];
        $finalLogMAR = $finalVisit['va_logmar'];
        $logmarChange = $finalLogMAR - $initialLogMAR;

        // Calculate percentage change
        // Note: Lower LogMAR = better vision, so improvement = decrease in LogMAR
        $percentageChange = null;
        if ($initialLogMAR > 0) {
            $percentageChange = (($initialLogMAR - $finalLogMAR) / $initialLogMAR) * 100;
        } else {
            // Handle case where initial LogMAR is 0 (perfect vision)
            if ($finalLogMAR > 0) {
                $percentageChange = -100; // Worsening from perfect vision
            } else {
                $percentageChange = 0; // Stable at perfect vision
            }
        }

        // Determine trend
        $trend = 'stable';
        if ($logmarChange <= -0.1) {
            $trend = 'improving'; // LogMAR decreased (vision improved)
        } elseif ($logmarChange >= 0.1) {
            $trend = 'worsening'; // LogMAR increased (vision worsened)
        }

        // Generate clinical note
        $clinicalNote = $this->generateEyeClinicalNote($eye, $initialVisit, $finalVisit, $logmarChange, $trend, $percentageChange);

        return [
            'visits' => $visits,
            'initial_va_logmar' => round($initialLogMAR, 3),
            'final_va_logmar' => round($finalLogMAR, 3),
            'initial_va_snellen' => $initialVisit['va_snellen'],
            'final_va_snellen' => $finalVisit['va_snellen'],
            'logmar_change' => round($logmarChange, 3),
            'percentage_change' => $percentageChange !== null ? round($percentageChange, 1) : null,
            'trend' => $trend,
            'clinical_note' => $clinicalNote
        ];
    }

    /**
     * Convert visual acuity to LogMAR
     * 
     * @param string|float $vaValue Visual acuity value (Snellen format or LogMAR)
     * @param string $format Format: 'snellen' or 'logmar'
     * @return float|null LogMAR value or null if invalid
     */
    private function convertToLogMAR($vaValue, string $format): ?float
    {
        if ($format === 'logmar') {
            $logmar = is_numeric($vaValue) ? (float)$vaValue : null;
            if ($logmar !== null && $logmar >= -0.3 && $logmar <= 3.0) {
                return $logmar;
            }
            return null;
        }

        // Snellen format
        if ($format === 'snellen') {
            return $this->snellenToLogMAR($vaValue);
        }

        return null;
    }

    /**
     * Convert Snellen format to LogMAR
     * 
     * Supports formats like: "6/6", "20/20", "6/12", "6/60", etc.
     * 
     * @param string $snellen Snellen format string
     * @return float|null LogMAR value or null if invalid
     */
    private function snellenToLogMAR(string $snellen): ?float
    {
        // Remove all spaces and convert to lowercase
        $snellen = strtolower(str_replace(' ', '', trim($snellen)));
        
        // Handle special cases
        if ($snellen === 'cf' || $snellen === 'counting fingers') {
            return 1.9; // Approximate LogMAR for counting fingers
        }
        if ($snellen === 'hm' || $snellen === 'hand movements') {
            return 2.3; // Approximate LogMAR for hand movements
        }
        if ($snellen === 'lp' || $snellen === 'light perception') {
            return 2.7; // Approximate LogMAR for light perception
        }
        if ($snellen === 'nlp' || $snellen === 'no light perception') {
            return 3.0; // Maximum LogMAR
        }

        // Parse Snellen format (e.g., "6/6", "20/20", "6/12")
        if (preg_match('/^(\d+)\/(\d+)$/', $snellen, $matches)) {
            $numerator = (float)$matches[1];
            $denominator = (float)$matches[2];
            
            if ($denominator > 0) {
                $fraction = $numerator / $denominator;
                if ($fraction > 0) {
                    $logmar = -log10($fraction);
                    // Clamp to valid range
                    if ($logmar >= -0.3 && $logmar <= 3.0) {
                        return $logmar;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Convert LogMAR to Snellen format for display
     * 
     * @param float $logmar LogMAR value
     * @return string Snellen format string
     */
    private function logmarToSnellen(float $logmar): string
    {
        // Validate input
        if (!is_numeric($logmar)) {
            return 'N/A';
        }
        
        // Handle special cases
        if ($logmar >= 2.7) {
            return 'LP';
        }
        if ($logmar >= 2.3) {
            return 'HM';
        }
        if ($logmar >= 1.9) {
            return 'CF';
        }

        // Convert LogMAR to Snellen fraction
        $fraction = pow(10, -$logmar);
        
        if ($fraction <= 0) {
            return 'N/A';
        }
        
        // Convert to standard Snellen notation (6-meter equivalent)
        $denominator = 6 / $fraction;
        
        if ($denominator <= 0) {
            return 'N/A';
        }
        
        // Round to nearest standard Snellen line
        $standardDenominators = [3, 4, 5, 6, 9, 12, 15, 18, 24, 30, 36, 48, 60];
        $closestDenominator = $standardDenominators[0];
        $minDiff = abs($denominator - $closestDenominator);
        
        foreach ($standardDenominators as $std) {
            $diff = abs($denominator - $std);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestDenominator = $std;
            }
        }
        
        return "6/{$closestDenominator}";
    }

    /**
     * Determine overall trend from both eyes
     * 
     * @param array $odResults OD eye results
     * @param array $osResults OS eye results
     * @return string Overall trend
     */
    private function determineOverallTrend(array $odResults, array $osResults): string
    {
        $odTrend = $odResults['trend'] ?? 'insufficient_data';
        $osTrend = $osResults['trend'] ?? 'insufficient_data';

        // If both eyes improving
        if ($odTrend === 'improving' && $osTrend === 'improving') {
            return 'improving';
        }

        // If both eyes worsening
        if ($odTrend === 'worsening' && $osTrend === 'worsening') {
            return 'worsening';
        }

        // If one improving and one worsening
        if (($odTrend === 'improving' && $osTrend === 'worsening') || 
            ($odTrend === 'worsening' && $osTrend === 'improving')) {
            return 'mixed';
        }

        // If both stable
        if ($odTrend === 'stable' && $osTrend === 'stable') {
            return 'stable';
        }

        // Default to stable if insufficient data
        return 'stable';
    }

    /**
     * Generate clinical note for a single eye
     * 
     * @param string $eye Eye identifier
     * @param array $initialVisit Initial visit data
     * @param array $finalVisit Final visit data
     * @param float $logmarChange LogMAR change
     * @param string $trend Trend classification
     * @param float|null $percentageChange Percentage change
     * @return string Clinical note
     */
    private function generateEyeClinicalNote(string $eye, array $initialVisit, array $finalVisit, float $logmarChange, string $trend, ?float $percentageChange): string
    {
        $initialDate = date('M d, Y', strtotime($initialVisit['date']));
        $finalDate = date('M d, Y', strtotime($finalVisit['date']));
        
        $note = "{$eye} eye: ";
        
        if ($trend === 'improving') {
            $note .= sprintf("Visual acuity improved from %s to %s between %s and %s. ", 
                $initialVisit['va_snellen'], 
                $finalVisit['va_snellen'],
                $initialDate,
                $finalDate
            );
            if ($percentageChange !== null) {
                $note .= sprintf("LogMAR decreased by %.3f (%.1f%% improvement). ", abs($logmarChange), abs($percentageChange));
            }
        } elseif ($trend === 'worsening') {
            $note .= sprintf("Visual acuity worsened from %s to %s between %s and %s. ", 
                $initialVisit['va_snellen'], 
                $finalVisit['va_snellen'],
                $initialDate,
                $finalDate
            );
            if ($percentageChange !== null) {
                $note .= sprintf("LogMAR increased by %.3f (%.1f%% deterioration). ", abs($logmarChange), abs($percentageChange));
            }
        } else {
            $note .= sprintf("Visual acuity remained stable at %s between %s and %s. ", 
                $initialVisit['va_snellen'],
                $initialDate,
                $finalDate
            );
        }

        return $note;
    }

    /**
     * Generate summary note for both eyes
     * 
     * @param array $odResults OD eye results
     * @param array $osResults OS eye results
     * @param string $overallTrend Overall trend
     * @return string Summary note
     */
    private function generateSummaryNote(array $odResults, array $osResults, string $overallTrend): string
    {
        $note = "Visual acuity progress analysis: ";
        
        if ($overallTrend === 'improving') {
            $note .= "Both eyes showing improvement. ";
        } elseif ($overallTrend === 'worsening') {
            $note .= "Both eyes showing deterioration. ";
        } elseif ($overallTrend === 'mixed') {
            $note .= "Mixed results: one eye improving, one eye worsening. ";
        } else {
            $note .= "Visual acuity remains stable in both eyes. ";
        }

        $odTrend = $odResults['trend'] ?? 'insufficient_data';
        $osTrend = $osResults['trend'] ?? 'insufficient_data';

        if ($odTrend !== 'insufficient_data' && $osTrend !== 'insufficient_data') {
            $note .= "OD: " . ($odResults['clinical_note'] ?? '') . " ";
            $note .= "OS: " . ($osResults['clinical_note'] ?? '') . " ";
        }

        return trim($note);
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

        if (!isset($data['visits']) || !is_array($data['visits'])) {
            $errors[] = 'Visits data is required and must be an array';
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        $visits = $data['visits'];

        if (count($visits) < 2) {
            $errors[] = 'At least 2 visits are required for progress calculation';
        }

        foreach ($visits as $index => $visit) {
            // Validate eye
            if (!isset($visit['eye']) || !in_array(strtoupper($visit['eye']), ['OD', 'OS'])) {
                $errors[] = "Visit " . ($index + 1) . ": Eye must be 'OD' or 'OS'";
            }

            // Validate VA value
            if (!isset($visit['va_value']) || $visit['va_value'] === null || $visit['va_value'] === '') {
                $errors[] = "Visit " . ($index + 1) . ": Visual acuity value is required";
            }

            // Validate VA format
            if (!isset($visit['va_format']) || !in_array(strtolower($visit['va_format']), ['snellen', 'logmar'])) {
                $errors[] = "Visit " . ($index + 1) . ": VA format must be 'snellen' or 'logmar'";
            }

            // Validate date
            if (!isset($visit['date']) || $visit['date'] === null || $visit['date'] === '') {
                $errors[] = "Visit " . ($index + 1) . ": Date is required";
            } else {
                $date = strtotime($visit['date']);
                if ($date === false) {
                    $errors[] = "Visit " . ($index + 1) . ": Invalid date format. Use Y-m-d format (e.g., 2024-01-15)";
                }
            }

            // Validate VA value format
            if (isset($visit['va_format']) && strtolower($visit['va_format']) === 'logmar') {
                if (!is_numeric($visit['va_value'])) {
                    $errors[] = "Visit " . ($index + 1) . ": LogMAR value must be numeric";
                } else {
                    $logmar = (float)$visit['va_value'];
                    if ($logmar < -0.3 || $logmar > 3.0) {
                        $errors[] = "Visit " . ($index + 1) . ": LogMAR value must be between -0.3 and 3.0";
                    }
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
        return 'Visual Acuity Progress Calculator';
    }
}


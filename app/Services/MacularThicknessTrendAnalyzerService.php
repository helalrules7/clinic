<?php

namespace App\Services;

/**
 * Macular Thickness Trend Analyzer Service
 * 
 * Analyzes central macular thickness measurements over time to determine trends
 * and generate clinical alerts for worsening conditions.
 * 
 * Clinical Logic:
 * - Trend classification: Stable, Improving, Worsening
 * - Alert thresholds for progressive increases and critical values
 * - Per-eye analysis (OD/OS independently)
 */
class MacularThicknessTrendAnalyzerService implements AnalyzerInterface
{
    /**
     * Analyze macular thickness trends
     * 
     * @param array $data Input data containing:
     *   - visits (array): Array of visit data, each containing:
     *     - eye (string): 'OD' or 'OS'
     *     - central_macular_thickness (float): Thickness in microns
     *     - date (string): Measurement date (Y-m-d format)
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

        $visits = $data['visits'];
        
        // Separate visits by eye
        $odVisits = [];
        $osVisits = [];

        foreach ($visits as $visit) {
            $eye = strtoupper($visit['eye']);
            $thickness = (float)$visit['central_macular_thickness'];
            $date = $visit['date'];

            $visitData = [
                'eye' => $eye,
                'central_macular_thickness' => $thickness,
                'date' => $date
            ];

            if ($eye === 'OD') {
                $odVisits[] = $visitData;
            } elseif ($eye === 'OS') {
                $osVisits[] = $visitData;
            }
        }

        // Sort visits by date
        usort($odVisits, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        usort($osVisits, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        // Analyze each eye independently
        $odAnalysis = $this->analyzeEye($odVisits, 'OD');
        $osAnalysis = $this->analyzeEye($osVisits, 'OS');

        // Generate alerts
        $alerts = $this->generateAlerts($odAnalysis, $osAnalysis);

        // Generate clinical summary
        $clinicalSummary = $this->generateClinicalSummary($odAnalysis, $osAnalysis, $alerts);

        return [
            'success' => true,
            'od' => $odAnalysis,
            'os' => $osAnalysis,
            'alerts' => $alerts,
            'clinical_summary' => $clinicalSummary
        ];
    }

    /**
     * Analyze macular thickness for one eye
     * 
     * @param array $visits Array of visits for the eye
     * @param string $eye Eye identifier ('OD' or 'OS')
     * @return array Analysis results
     */
    private function analyzeEye(array $visits, string $eye): array
    {
        if (count($visits) < 2) {
            return [
                'eye' => $eye,
                'visits' => $visits,
                'trend' => 'insufficient_data',
                'baseline_thickness' => count($visits) > 0 ? $visits[0]['central_macular_thickness'] : null,
                'latest_thickness' => count($visits) > 0 ? $visits[count($visits) - 1]['central_macular_thickness'] : null,
                'change_from_baseline' => null,
                'percent_change' => null,
                'alerts' => []
            ];
        }

        $baselineThickness = $visits[0]['central_macular_thickness'];
        $latestThickness = $visits[count($visits) - 1]['central_macular_thickness'];
        $changeFromBaseline = $latestThickness - $baselineThickness;
        $percentChange = ($baselineThickness > 0) ? (($changeFromBaseline / $baselineThickness) * 100) : 0;

        // Determine trend
        $trend = $this->classifyTrend($changeFromBaseline, $percentChange);

        // Check for alerts
        $alerts = $this->checkEyeAlerts($visits, $baselineThickness, $latestThickness, $changeFromBaseline);

        return [
            'eye' => $eye,
            'visits' => $visits,
            'trend' => $trend,
            'baseline_thickness' => round($baselineThickness, 2),
            'latest_thickness' => round($latestThickness, 2),
            'change_from_baseline' => round($changeFromBaseline, 2),
            'percent_change' => round($percentChange, 2),
            'alerts' => $alerts
        ];
    }

    /**
     * Classify trend based on change
     * 
     * @param float $change Change in microns from baseline
     * @param float $percentChange Percent change from baseline
     * @return string Trend classification
     */
    private function classifyTrend(float $change, float $percentChange): string
    {
        // Stable: Change <10% or <20µm from baseline
        if (abs($percentChange) < 10 && abs($change) < 20) {
            return 'stable';
        }
        // Improving: Decrease ≥10% or ≥20µm
        elseif ($change < -20 || $percentChange <= -10) {
            return 'improving';
        }
        // Worsening: Increase ≥10% or ≥20µm
        else {
            return 'worsening';
        }
    }

    /**
     * Check for alerts in eye measurements
     * 
     * @param array $visits Array of visits
     * @param float $baselineThickness Baseline thickness
     * @param float $latestThickness Latest thickness
     * @param float $changeFromBaseline Change from baseline
     * @return array Array of alert messages
     */
    private function checkEyeAlerts(array $visits, float $baselineThickness, float $latestThickness, float $changeFromBaseline): array
    {
        $alerts = [];

        // Alert: Thickness >300µm (potential macular edema)
        if ($latestThickness > 300) {
            $alerts[] = [
                'type' => 'critical',
                'message' => sprintf('Central macular thickness >300µm (%s µm) - potential macular edema', round($latestThickness, 2))
            ];
        }

        // Alert: Progressive increase >50µm over 3 months
        if (count($visits) >= 2) {
            $firstDate = strtotime($visits[0]['date']);
            $lastDate = strtotime($visits[count($visits) - 1]['date']);
            $monthsDiff = (($lastDate - $firstDate) / (30 * 24 * 60 * 60));
            
            if ($monthsDiff <= 3 && $changeFromBaseline > 50) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => sprintf('Progressive increase >50µm over %.1f months - requires close monitoring', round($monthsDiff, 1))
                ];
            }
        }

        // Alert: Single increase >100µm from previous measurement
        if (count($visits) >= 2) {
            $previousThickness = $visits[count($visits) - 2]['central_macular_thickness'];
            $singleChange = $latestThickness - $previousThickness;
            
            if ($singleChange > 100) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => sprintf('Significant increase >100µm from previous measurement (%s µm increase)', round($singleChange, 2))
                ];
            }
        }

        return $alerts;
    }

    /**
     * Generate alerts across both eyes
     * 
     * @param array $odAnalysis OD analysis results
     * @param array $osAnalysis OS analysis results
     * @return array Combined alerts
     */
    private function generateAlerts(array $odAnalysis, array $osAnalysis): array
    {
        $alerts = [];

        // Add eye-specific alerts
        if (!empty($odAnalysis['alerts'])) {
            foreach ($odAnalysis['alerts'] as $alert) {
                $alerts[] = [
                    'eye' => 'OD',
                    'type' => $alert['type'],
                    'message' => $alert['message']
                ];
            }
        }

        if (!empty($osAnalysis['alerts'])) {
            foreach ($osAnalysis['alerts'] as $alert) {
                $alerts[] = [
                    'eye' => 'OS',
                    'type' => $alert['type'],
                    'message' => $alert['message']
                ];
            }
        }

        return $alerts;
    }

    /**
     * Generate clinical summary
     * 
     * @param array $odAnalysis OD analysis results
     * @param array $osAnalysis OS analysis results
     * @param array $alerts Combined alerts
     * @return string Clinical summary
     */
    private function generateClinicalSummary(array $odAnalysis, array $osAnalysis, array $alerts): string
    {
        $summary = "Macular thickness trend analysis: ";

        // OD summary
        if ($odAnalysis['trend'] !== 'insufficient_data') {
            $summary .= sprintf("OD: %s trend (baseline: %s µm, latest: %s µm, change: %s µm [%s%%]). ", 
                ucfirst($odAnalysis['trend']),
                $odAnalysis['baseline_thickness'],
                $odAnalysis['latest_thickness'],
                $odAnalysis['change_from_baseline'] > 0 ? '+' : '',
                $odAnalysis['change_from_baseline'],
                $odAnalysis['percent_change'] > 0 ? '+' : '',
                $odAnalysis['percent_change']
            );
        }

        // OS summary
        if ($osAnalysis['trend'] !== 'insufficient_data') {
            $summary .= sprintf("OS: %s trend (baseline: %s µm, latest: %s µm, change: %s µm [%s%%]). ", 
                ucfirst($osAnalysis['trend']),
                $osAnalysis['baseline_thickness'],
                $osAnalysis['latest_thickness'],
                $osAnalysis['change_from_baseline'] > 0 ? '+' : '',
                $osAnalysis['change_from_baseline'],
                $osAnalysis['percent_change'] > 0 ? '+' : '',
                $osAnalysis['percent_change']
            );
        }

        // Add alerts if any
        if (!empty($alerts)) {
            $summary .= "Alerts: ";
            $alertMessages = array_map(function($alert) {
                return $alert['eye'] . ': ' . $alert['message'];
            }, $alerts);
            $summary .= implode('; ', $alertMessages) . ". ";
        }

        $summary .= "Continue monitoring as clinically indicated.";

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

        if (!isset($data['visits']) || !is_array($data['visits'])) {
            $errors[] = 'Visits data is required and must be an array';
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        if (count($data['visits']) < 2) {
            $errors[] = 'At least 2 visits are required for trend analysis';
        }

        foreach ($data['visits'] as $index => $visit) {
            if (!isset($visit['eye']) || empty($visit['eye'])) {
                $errors[] = "Visit " . ($index + 1) . ": Eye (OD/OS) is required";
            } elseif (!in_array(strtoupper($visit['eye']), ['OD', 'OS'])) {
                $errors[] = "Visit " . ($index + 1) . ": Eye must be OD or OS";
            }

            if (!isset($visit['central_macular_thickness']) || $visit['central_macular_thickness'] === null || $visit['central_macular_thickness'] === '') {
                $errors[] = "Visit " . ($index + 1) . ": Central macular thickness is required";
            } else {
                if (!is_numeric($visit['central_macular_thickness'])) {
                    $errors[] = "Visit " . ($index + 1) . ": Central macular thickness must be numeric";
                } else {
                    $thickness = (float)$visit['central_macular_thickness'];
                    if ($thickness < 100 || $thickness > 1000) {
                        $errors[] = "Visit " . ($index + 1) . ": Central macular thickness must be between 100 and 1000 microns";
                    }
                }
            }

            if (!isset($visit['date']) || empty($visit['date'])) {
                $errors[] = "Visit " . ($index + 1) . ": Measurement date is required";
            } else {
                $date = strtotime($visit['date']);
                if ($date === false) {
                    $errors[] = "Visit " . ($index + 1) . ": Invalid date format. Use Y-m-d format (e.g., 2024-01-15)";
                }
            }
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
        return 'Macular Thickness Trend Analyzer';
    }
}


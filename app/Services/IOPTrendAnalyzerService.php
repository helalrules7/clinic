<?php

namespace App\Services;

/**
 * IOP Trend Analyzer Service
 * 
 * Analyzes intraocular pressure (IOP) readings over time to determine
 * trends, detect spikes, and evaluate treatment response.
 * 
 * Performs per-eye analysis independently (OD and OS).
 */
class IOPTrendAnalyzerService implements IOPTrendAnalyzerInterface
{
    /**
     * Analyze IOP readings and calculate trends
     * 
     * @param array $readings Array of IOP readings
     * @return array Analysis results per eye
     */
    public function analyze(array $readings): array
    {
        $validation = $this->validate($readings);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Separate readings by eye
        $odReadings = [];
        $osReadings = [];

        foreach ($readings as $reading) {
            if (isset($reading['eye']) && isset($reading['iop']) && isset($reading['date'])) {
                if ($reading['eye'] === 'OD') {
                    $odReadings[] = $reading;
                } elseif ($reading['eye'] === 'OS') {
                    $osReadings[] = $reading;
                }
            }
        }

        // Analyze each eye independently
        $odAnalysis = $this->analyzeEye($odReadings, 'OD');
        $osAnalysis = $this->analyzeEye($osReadings, 'OS');

        // Generate clinical alerts
        $alerts = $this->generateAlerts($odAnalysis, $osAnalysis);

        return [
            'success' => true,
            'od' => $odAnalysis,
            'os' => $osAnalysis,
            'alerts' => $alerts
        ];
    }

    /**
     * Validate IOP readings data
     * 
     * @param array $readings Array of IOP readings
     * @return array Validation result
     */
    public function validate(array $readings): array
    {
        $errors = [];

        if (empty($readings)) {
            $errors[] = "No IOP readings provided";
            return ['valid' => false, 'errors' => $errors];
        }

        // Validate chronological order
        $dates = [];
        foreach ($readings as $index => $reading) {
            if (!isset($reading['date'])) {
                $errors[] = "Reading at index {$index} is missing date";
                continue;
            }

            if (!isset($reading['eye']) || !in_array($reading['eye'], ['OD', 'OS'])) {
                $errors[] = "Reading at index {$index} has invalid eye value";
                continue;
            }

            if (!isset($reading['iop'])) {
                $errors[] = "Reading at index {$index} is missing IOP value";
                continue;
            }

            // Validate IOP range (5-60 mmHg)
            $iop = is_numeric($reading['iop']) ? (float)$reading['iop'] : null;
            if ($iop === null) {
                $errors[] = "Reading at index {$index} has invalid IOP value";
                continue;
            }

            if ($iop < 5.0 || $iop > 60.0) {
                $errors[] = "Reading at index {$index} has IOP value {$iop} mmHg outside physiologic range (5-60 mmHg)";
                continue;
            }

            $dates[] = [
                'index' => $index,
                'date' => $reading['date'],
                'timestamp' => strtotime($reading['date'])
            ];
        }

        // Check chronological order
        if (count($dates) > 1) {
            $previousTimestamp = null;
            foreach ($dates as $dateInfo) {
                if ($previousTimestamp !== null && $dateInfo['timestamp'] < $previousTimestamp) {
                    $errors[] = "Readings are not in chronological order. Reading at index {$dateInfo['index']} has date {$dateInfo['date']} which is earlier than previous reading";
                }
                $previousTimestamp = $dateInfo['timestamp'];
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Analyze IOP readings for a single eye
     * 
     * @param array $readings Array of readings for one eye
     * @param string $eye 'OD' or 'OS'
     * @return array Analysis results
     */
    private function analyzeEye(array $readings, string $eye): array
    {
        if (empty($readings)) {
            return [
                'mean_iop' => null,
                'peak_iop' => null,
                'rate_of_change' => null,
                'trend' => null,
                'spikes' => [],
                'treatment_response' => null,
                'readings' => []
            ];
        }

        // Sort by date to ensure chronological order
        usort($readings, function($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        // Extract IOP values and dates
        $iopValues = array_map(function($r) { return (float)$r['iop']; }, $readings);
        $dates = array_map(function($r) { return $r['date']; }, $readings);

        // Calculate mean IOP
        $meanIOP = array_sum($iopValues) / count($iopValues);

        // Calculate peak IOP
        $peakIOP = max($iopValues);

        // Calculate rate of change using linear regression
        $rateOfChange = $this->calculateRateOfChange($dates, $iopValues);

        // Classify trend
        $trend = $this->classifyTrend($rateOfChange);

        // Detect spikes
        $spikes = $this->detectSpikes($readings);

        // Analyze treatment response
        $treatmentResponse = $this->analyzeTreatmentResponse($readings);

        return [
            'mean_iop' => round($meanIOP, 2),
            'peak_iop' => round($peakIOP, 2),
            'rate_of_change' => round($rateOfChange, 2),
            'trend' => $trend,
            'spikes' => $spikes,
            'treatment_response' => $treatmentResponse,
            'readings' => $readings
        ];
    }

    /**
     * Calculate rate of change using linear regression (least squares method)
     * 
     * @param array $dates Array of date strings
     * @param array $iopValues Array of IOP values
     * @return float Rate of change in mmHg/year
     */
    private function calculateRateOfChange(array $dates, array $iopValues): float
    {
        if (count($dates) < 2) {
            return 0.0;
        }

        $n = count($dates);
        
        // Convert dates to days since first reading
        $firstDate = strtotime($dates[0]);
        $xValues = [];
        foreach ($dates as $date) {
            $daysSinceFirst = (strtotime($date) - $firstDate) / 86400; // Convert to days
            $xValues[] = $daysSinceFirst;
        }

        // Calculate linear regression using least squares method
        // Formula: slope = (n*Σ(xy) - Σ(x)*Σ(y)) / (n*Σ(x²) - (Σ(x))²)
        $sumX = array_sum($xValues);
        $sumY = array_sum($iopValues);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $xValues[$i] * $iopValues[$i];
            $sumX2 += $xValues[$i] * $xValues[$i];
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        
        if ($denominator == 0) {
            return 0.0;
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;

        // Convert from mmHg/day to mmHg/year
        $ratePerYear = $slope * 365.25;

        return $ratePerYear;
    }

    /**
     * Classify trend based on rate of change
     * 
     * @param float $rateOfChange Rate of change in mmHg/year
     * @return string 'stable', 'improving', or 'worsening'
     */
    private function classifyTrend(float $rateOfChange): string
    {
        if ($rateOfChange < -1.0) {
            return 'improving';
        } elseif ($rateOfChange > 1.0) {
            return 'worsening';
        } else {
            return 'stable';
        }
    }

    /**
     * Detect sudden IOP spikes (increase ≥ 5 mmHg)
     * 
     * @param array $readings Array of readings in chronological order
     * @return array Array of detected spikes
     */
    private function detectSpikes(array $readings): array
    {
        $spikes = [];

        for ($i = 1; $i < count($readings); $i++) {
            $currentIOP = (float)$readings[$i]['iop'];
            $previousIOP = (float)$readings[$i - 1]['iop'];
            $increase = $currentIOP - $previousIOP;

            if ($increase >= 5.0) {
                $spikes[] = [
                    'date' => $readings[$i]['date'],
                    'iop' => $currentIOP,
                    'previous_iop' => $previousIOP,
                    'increase' => round($increase, 2)
                ];
            }
        }

        return $spikes;
    }

    /**
     * Analyze treatment response
     * 
     * @param array $readings Array of readings in chronological order
     * @return array|null Treatment response analysis or null if insufficient data
     */
    private function analyzeTreatmentResponse(array $readings): ?array
    {
        if (empty($readings)) {
            return null;
        }

        // Identify baseline IOP (first reading)
        $baselineIOP = (float)$readings[0]['iop'];

        // Identify treatment initiation
        // Check if medication field exists and changes from empty to non-empty
        $treatmentStartIndex = null;
        for ($i = 0; $i < count($readings); $i++) {
            if (isset($readings[$i]['medication']) && !empty($readings[$i]['medication'])) {
                $treatmentStartIndex = $i;
                break;
            }
        }

        // If no treatment detected, use first reading as baseline
        if ($treatmentStartIndex === null) {
            return [
                'baseline_iop' => round($baselineIOP, 2),
                'current_mean' => round($baselineIOP, 2),
                'reduction_percent' => 0.0,
                'poor_response' => false,
                'message' => 'No treatment detected in consultation notes'
            ];
        }

        // Calculate baseline as average of pre-treatment readings
        if ($treatmentStartIndex > 0) {
            $preTreatmentIOPs = [];
            for ($i = 0; $i < $treatmentStartIndex; $i++) {
                $preTreatmentIOPs[] = (float)$readings[$i]['iop'];
            }
            $baselineIOP = array_sum($preTreatmentIOPs) / count($preTreatmentIOPs);
        }

        // Calculate current mean (post-treatment)
        $postTreatmentIOPs = [];
        for ($i = $treatmentStartIndex; $i < count($readings); $i++) {
            $postTreatmentIOPs[] = (float)$readings[$i]['iop'];
        }
        $currentMean = array_sum($postTreatmentIOPs) / count($postTreatmentIOPs);

        // Calculate reduction percentage
        $reductionPercent = (($baselineIOP - $currentMean) / $baselineIOP) * 100;

        // Flag poor response if reduction < 20%
        $poorResponse = $reductionPercent < 20.0;

        $message = $poorResponse 
            ? "Poor treatment response: Only " . round($reductionPercent, 1) . "% reduction from baseline. Consider treatment adjustment."
            : "Good treatment response: " . round($reductionPercent, 1) . "% reduction from baseline.";

        return [
            'baseline_iop' => round($baselineIOP, 2),
            'current_mean' => round($currentMean, 2),
            'reduction_percent' => round($reductionPercent, 2),
            'poor_response' => $poorResponse,
            'message' => $message
        ];
    }

    /**
     * Generate clinical alerts based on analysis results
     * 
     * @param array $odAnalysis OD eye analysis results
     * @param array $osAnalysis OS eye analysis results
     * @return array Array of clinical alerts
     */
    private function generateAlerts(array $odAnalysis, array $osAnalysis): array
    {
        $alerts = [];

        // Check for spikes
        if (!empty($odAnalysis['spikes'])) {
            foreach ($odAnalysis['spikes'] as $spike) {
                $alerts[] = [
                    'type' => 'spike',
                    'eye' => 'OD',
                    'message' => "IOP spike detected in OD: Increased from {$spike['previous_iop']} to {$spike['iop']} mmHg (+{$spike['increase']} mmHg) on {$spike['date']}"
                ];
            }
        }

        if (!empty($osAnalysis['spikes'])) {
            foreach ($osAnalysis['spikes'] as $spike) {
                $alerts[] = [
                    'type' => 'spike',
                    'eye' => 'OS',
                    'message' => "IOP spike detected in OS: Increased from {$spike['previous_iop']} to {$spike['iop']} mmHg (+{$spike['increase']} mmHg) on {$spike['date']}"
                ];
            }
        }

        // Check for poor treatment response
        if ($odAnalysis['treatment_response'] && $odAnalysis['treatment_response']['poor_response']) {
            $alerts[] = [
                'type' => 'poor_response',
                'eye' => 'OD',
                'message' => $odAnalysis['treatment_response']['message']
            ];
        }

        if ($osAnalysis['treatment_response'] && $osAnalysis['treatment_response']['poor_response']) {
            $alerts[] = [
                'type' => 'poor_response',
                'eye' => 'OS',
                'message' => $osAnalysis['treatment_response']['message']
            ];
        }

        // Check for worsening trends
        if ($odAnalysis['trend'] === 'worsening') {
            $alerts[] = [
                'type' => 'worsening',
                'eye' => 'OD',
                'message' => "Worsening IOP trend in OD: Rate of change is {$odAnalysis['rate_of_change']} mmHg/year. Consider treatment adjustment."
            ];
        }

        if ($osAnalysis['trend'] === 'worsening') {
            $alerts[] = [
                'type' => 'worsening',
                'eye' => 'OS',
                'message' => "Worsening IOP trend in OS: Rate of change is {$osAnalysis['rate_of_change']} mmHg/year. Consider treatment adjustment."
            ];
        }

        return $alerts;
    }
}


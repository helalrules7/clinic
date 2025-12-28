<?php

namespace App\Services;

/**
 * IOL Calculator Service
 * 
 * Orchestrates all three IOL power calculation formulas and provides
 * a unified interface for calculating IOL power using multiple methods.
 */
class IOLCalculatorService
{
    private $srktCalculator;
    private $hofferQCalculator;
    private $holladay1Calculator;

    public function __construct()
    {
        $this->srktCalculator = new SRKTCalculator();
        $this->hofferQCalculator = new HofferQCalculator();
        $this->holladay1Calculator = new Holladay1Calculator();
    }

    /**
     * Calculate IOL power using all three formulas
     * 
     * @param array $data Input data for calculations
     * @return array Comprehensive results from all formulas
     */
    public function calculateAll(array $data): array
    {
        // Run all three calculations
        $srktResult = $this->srktCalculator->calculate($data);
        $hofferQResult = $this->hofferQCalculator->calculate($data);
        $holladay1Result = $this->holladay1Calculator->calculate($data);

        // Determine AL warning
        $axialLength = isset($data['axial_length']) ? (float)$data['axial_length'] : 0;
        $alWarning = $this->determineALWarning($axialLength);
        $alWarningMessage = $this->getALWarningMessage($axialLength);

        // Calculate suggested IOL power (average of valid results)
        $validPowers = [];
        if ($srktResult['power'] !== null) {
            $validPowers[] = $srktResult['power'];
        }
        if ($hofferQResult['power'] !== null) {
            $validPowers[] = $hofferQResult['power'];
        }
        if ($holladay1Result['power'] !== null) {
            $validPowers[] = $holladay1Result['power'];
        }

        $suggestedPower = null;
        if (!empty($validPowers)) {
            $average = array_sum($validPowers) / count($validPowers);
            $suggestedPower = $this->roundToLensStep($average);
        }

        return [
            'success' => true,
            'results' => [
                'srkt' => [
                    'power' => $srktResult['power'],
                    'expected_refraction' => $srktResult['expected_refraction'],
                    'warnings' => $srktResult['warnings']
                ],
                'hoffer_q' => [
                    'power' => $hofferQResult['power'],
                    'expected_refraction' => $hofferQResult['expected_refraction'],
                    'warnings' => $hofferQResult['warnings']
                ],
                'holladay_1' => [
                    'power' => $holladay1Result['power'],
                    'expected_refraction' => $holladay1Result['expected_refraction'],
                    'warnings' => $holladay1Result['warnings']
                ]
            ],
            'suggested_power' => $suggestedPower,
            'al_warning' => $alWarning,
            'al_warning_message' => $alWarningMessage
        ];
    }

    /**
     * Determine AL warning status
     * 
     * @param float $axialLength Axial length in mm
     * @return string Warning status: 'normal', 'short', or 'long'
     */
    private function determineALWarning(float $axialLength): string
    {
        if ($axialLength < 22.0) {
            return 'short';
        } elseif ($axialLength > 26.0) {
            return 'long';
        } else {
            return 'normal';
        }
    }

    /**
     * Get AL warning message
     * 
     * @param float $axialLength Axial length in mm
     * @return string Warning message
     */
    private function getALWarningMessage(float $axialLength): string
    {
        if ($axialLength < 22.0) {
            return "Short Eye (AL < 22.0 mm) - Consider Hoffer Q formula for better accuracy";
        } elseif ($axialLength > 26.0) {
            return "Long Eye (AL > 26.0 mm) - Consider SRK/T formula for better accuracy";
        } else {
            return "Normal Eye (22.0-26.0 mm) - All formulas are applicable";
        }
    }

    /**
     * Round IOL power to standard lens steps (0.5 D increments)
     * 
     * @param float $power IOL power in diopters
     * @return float Rounded power
     */
    private function roundToLensStep(float $power): float
    {
        // Round to nearest 0.5 D
        return round($power * 2) / 2;
    }
}

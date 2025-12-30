<?php

namespace App\Services;

/**
 * Shared Interface for All Analyzers
 * 
 * All analyzer implementations must implement this interface
 * to ensure consistent structure and behavior.
 */
interface AnalyzerInterface
{
    /**
     * Analyze data and provide clinical insights
     * 
     * @param array $data Input data specific to the analyzer
     * @return array Analysis results
     */
    public function analyze(array $data): array;

    /**
     * Validate input data for this analyzer
     * 
     * @param array $data Input data to validate
     * @return array Validation result:
     *   - valid (bool): Whether data is valid
     *   - errors (array): Array of error messages if invalid
     */
    public function validate(array $data): array;

    /**
     * Get the name of this analyzer
     * 
     * @return string Analyzer name (e.g., "Diabetic Retinopathy Risk Estimator", "Macular Thickness Trend Analyzer")
     */
    public function getAnalyzerName(): string;
}


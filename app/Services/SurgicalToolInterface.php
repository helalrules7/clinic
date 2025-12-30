<?php

namespace App\Services;

/**
 * Shared Interface for All Surgical Tools
 * 
 * All surgical tool implementations must implement this interface
 * to ensure consistent structure and behavior.
 */
interface SurgicalToolInterface
{
    /**
     * Analyze data and provide clinical insights
     * 
     * @param array $data Input data specific to the tool
     * @return array Analysis results
     */
    public function analyze(array $data): array;

    /**
     * Validate input data for this tool
     * 
     * @param array $data Input data to validate
     * @return array Validation result:
     *   - valid (bool): Whether data is valid
     *   - errors (array): Array of error messages if invalid
     */
    public function validate(array $data): array;

    /**
     * Get the name of this surgical tool
     * 
     * @return string Tool name (e.g., "Cataract Surgery Readiness Score", "Post-Operative Outcome Analyzer")
     */
    public function getToolName(): string;
}


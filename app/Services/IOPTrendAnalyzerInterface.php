<?php

namespace App\Services;

/**
 * Interface for IOP Trend Analyzers
 * 
 * All IOP trend analysis implementations must implement this interface
 * to ensure consistent structure and behavior.
 */
interface IOPTrendAnalyzerInterface
{
    /**
     * Analyze IOP readings and calculate trends
     * 
     * @param array $readings Array of IOP readings, each containing:
     *   - eye (string): 'OD' or 'OS'
     *   - iop (float): IOP value in mmHg
     *   - date (string): Measurement date in 'Y-m-d' format
     *   - appointment_id (int): Appointment ID
     * 
     * @return array Analysis result containing:
     *   - mean_iop (float): Mean IOP over the period
     *   - peak_iop (float): Peak IOP value
     *   - rate_of_change (float): Rate of change in mmHg/year
     *   - trend (string): 'stable', 'improving', or 'worsening'
     *   - spikes (array): Array of detected spikes
     *   - treatment_response (array): Treatment response analysis
     *   - readings (array): All readings for visualization
     */
    public function analyze(array $readings): array;

    /**
     * Validate IOP readings data
     * 
     * @param array $readings Array of IOP readings to validate
     * @return array Validation result:
     *   - valid (bool): Whether data is valid
     *   - errors (array): Array of error messages if invalid
     */
    public function validate(array $readings): array;
}


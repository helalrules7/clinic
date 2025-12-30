<?php

namespace App\Services;

use App\Config\Database;

/**
 * Clinical Data Parser Service
 * 
 * Aggregates clinical data from multiple sources (consultation_notes, osdi_results, etc.)
 * and returns standardized summary format for Unified Clinical Dashboard.
 */
class ClinicalDataParserService
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Get unified clinical snapshot for a patient
     * 
     * @param int $patientId Patient ID
     * @return array Standardized clinical summary
     */
    public function getClinicalSnapshot($patientId): array
    {
        $snapshot = [
            'iop' => $this->getIOPStatus($patientId),
            'visual_acuity' => $this->getVisualAcuityStatus($patientId),
            'cataract' => $this->getCataractStatus($patientId),
            'dry_eye' => $this->getDryEyeStatus($patientId),
            'macular_thickness' => $this->getMacularThicknessStatus($patientId),
            'alerts' => []
        ];

        // Generate alerts based on clinical data
        $snapshot['alerts'] = $this->generateAlerts($snapshot);

        return $snapshot;
    }

    /**
     * Get IOP status from consultation notes
     * 
     * @param int $patientId Patient ID
     * @return array IOP status data
     */
    private function getIOPStatus($patientId): array
    {
        try {
            // Get latest IOP measurements from consultation notes
            $stmt = $this->pdo->prepare("
                SELECT cn.IOP_right, cn.IOP_left, cn.created_at, a.date as appointment_date, a.id as appointment_id
                FROM consultation_notes cn
                JOIN appointments a ON cn.appointment_id = a.id
                WHERE a.patient_id = ? 
                AND (cn.IOP_right IS NOT NULL OR cn.IOP_left IS NOT NULL)
                ORDER BY a.date DESC, cn.created_at DESC
                LIMIT 2
            ");
            $stmt->execute([$patientId]);
            $iopData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($iopData)) {
                return [
                    'value' => null,
                    'target' => null,
                    'status' => 'normal',
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            // Get latest IOP (average of both eyes if available)
            $latest = $iopData[0];
            $iopValues = [];
            if (!empty($latest['IOP_right']) && is_numeric($latest['IOP_right'])) {
                $iopValues[] = (float)$latest['IOP_right'];
            }
            if (!empty($latest['IOP_left']) && is_numeric($latest['IOP_left'])) {
                $iopValues[] = (float)$latest['IOP_left'];
            }

            if (empty($iopValues)) {
                return [
                    'value' => null,
                    'target' => null,
                    'status' => 'normal',
                    'message' => 'Not available',
                    'appointment_id' => $latest['appointment_id']
                ];
            }

            $latestIOP = array_sum($iopValues) / count($iopValues);

            // Try to get target IOP from plan field or previous calculations
            $targetIOP = $this->extractTargetIOP($latest['appointment_id']);

            // Determine status
            $status = 'normal';
            $message = 'Within normal range';
            if ($targetIOP !== null && $latestIOP > $targetIOP) {
                $status = 'warning';
                $message = 'Above target IOP';
            } elseif ($latestIOP > 21) {
                $status = 'warning';
                $message = 'Elevated IOP';
            }

            return [
                'value' => round($latestIOP, 1),
                'target' => $targetIOP,
                'status' => $status,
                'message' => $message,
                'appointment_id' => $latest['appointment_id'],
                'date' => $latest['appointment_date']
            ];

        } catch (\Exception $e) {
            return [
                'value' => null,
                'target' => null,
                'status' => 'normal',
                'message' => 'Not available',
                'appointment_id' => null
            ];
        }
    }

    /**
     * Extract target IOP from plan field or consultation notes
     * 
     * @param int $appointmentId Appointment ID
     * @return float|null Target IOP value
     */
    private function extractTargetIOP($appointmentId): ?float
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT plan FROM consultation_notes 
                WHERE appointment_id = ?
                ORDER BY created_at DESC LIMIT 1
            ");
            $stmt->execute([$appointmentId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result && !empty($result['plan'])) {
                // Try to extract target IOP from plan text
                // Look for patterns like "Target IOP: 18" or "Target: 18 mmHg"
                if (preg_match('/target\s+iop[:\s]+(\d+(?:\.\d+)?)/i', $result['plan'], $matches)) {
                    return (float)$matches[1];
                }
                if (preg_match('/target[:\s]+(\d+(?:\.\d+)?)\s*mmhg/i', $result['plan'], $matches)) {
                    return (float)$matches[1];
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get Visual Acuity status from consultation notes
     * 
     * @param int $patientId Patient ID
     * @return array Visual Acuity status data
     */
    private function getVisualAcuityStatus($patientId): array
    {
        try {
            // Get last 2 Visual Acuity measurements
            $stmt = $this->pdo->prepare("
                SELECT cn.visual_acuity_right, cn.visual_acuity_left, a.date as appointment_date, a.id as appointment_id
                FROM consultation_notes cn
                JOIN appointments a ON cn.appointment_id = a.id
                WHERE a.patient_id = ? 
                AND (cn.visual_acuity_right IS NOT NULL OR cn.visual_acuity_left IS NOT NULL)
                ORDER BY a.date DESC, cn.created_at DESC
                LIMIT 2
            ");
            $stmt->execute([$patientId]);
            $vaData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($vaData)) {
                return [
                    'last' => null,
                    'previous' => null,
                    'trend' => '→',
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            // Convert Snellen to LogMAR for comparison
            $latestVA = $this->parseVisualAcuity($vaData[0]);
            $previousVA = isset($vaData[1]) ? $this->parseVisualAcuity($vaData[1]) : null;

            // Determine trend
            $trend = '→'; // stable
            $message = 'Stable';
            
            if ($previousVA !== null && $latestVA !== null) {
                $change = $latestVA - $previousVA;
                if ($change <= -0.1) {
                    $trend = '↓'; // improving (lower LogMAR is better)
                    $message = 'Improving';
                } elseif ($change >= 0.1) {
                    $trend = '↑'; // worsening
                    $message = 'Worsening';
                }
            }

            return [
                'last' => $this->formatVisualAcuity($vaData[0]),
                'previous' => isset($vaData[1]) ? $this->formatVisualAcuity($vaData[1]) : null,
                'trend' => $trend,
                'message' => $message,
                'appointment_id' => $vaData[0]['appointment_id'],
                'date' => $vaData[0]['appointment_date'],
                'logmar_last' => $latestVA,
                'logmar_previous' => $previousVA
            ];

        } catch (\Exception $e) {
            return [
                'last' => null,
                'previous' => null,
                'trend' => '→',
                'message' => 'Not available',
                'appointment_id' => null
            ];
        }
    }

    /**
     * Parse Visual Acuity string to LogMAR
     * 
     * @param array $vaData Visual Acuity data
     * @return float|null LogMAR value
     */
    private function parseVisualAcuity($vaData): ?float
    {
        $vaRight = $vaData['visual_acuity_right'] ?? null;
        $vaLeft = $vaData['visual_acuity_left'] ?? null;

        $logmarValues = [];
        
        if ($vaRight) {
            $logmar = $this->snellenToLogMAR($vaRight);
            if ($logmar !== null) {
                $logmarValues[] = $logmar;
            }
        }
        
        if ($vaLeft) {
            $logmar = $this->snellenToLogMAR($vaLeft);
            if ($logmar !== null) {
                $logmarValues[] = $logmar;
            }
        }

        if (empty($logmarValues)) {
            return null;
        }

        // Return average LogMAR
        return array_sum($logmarValues) / count($logmarValues);
    }

    /**
     * Convert Snellen format to LogMAR
     * 
     * @param string $snellen Snellen format (e.g., "6/6", "20/20")
     * @return float|null LogMAR value
     */
    private function snellenToLogMAR($snellen): ?float
    {
        if (empty($snellen)) {
            return null;
        }

        // Remove spaces
        $snellen = str_replace(' ', '', trim($snellen));
        
        // Match pattern like "6/6", "20/20", "6/12", etc.
        if (preg_match('/(\d+)\/(\d+)/', $snellen, $matches)) {
            $numerator = (float)$matches[1];
            $denominator = (float)$matches[2];
            
            if ($denominator > 0) {
                return log10($denominator / $numerator);
            }
        }

        // If already LogMAR format
        if (is_numeric($snellen)) {
            return (float)$snellen;
        }

        return null;
    }

    /**
     * Format Visual Acuity for display
     * 
     * @param array $vaData Visual Acuity data
     * @return string Formatted VA string
     */
    private function formatVisualAcuity($vaData): string
    {
        $vaRight = $vaData['visual_acuity_right'] ?? null;
        $vaLeft = $vaData['visual_acuity_left'] ?? null;

        if ($vaRight && $vaLeft) {
            return "OD: {$vaRight} | OS: {$vaLeft}";
        } elseif ($vaRight) {
            return "OD: {$vaRight}";
        } elseif ($vaLeft) {
            return "OS: {$vaLeft}";
        }

        return 'Not available';
    }

    /**
     * Get Cataract status from cataract_surgery_readiness_results
     * 
     * @param int $patientId Patient ID
     * @return array Cataract status data
     */
    private function getCataractStatus($patientId): array
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'cataract_surgery_readiness_results'");
            if ($stmt->rowCount() === 0) {
                return [
                    'readiness' => null,
                    'status' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            $stmt = $this->pdo->prepare("
                SELECT readiness_classification, readiness_score, a.id as appointment_id, a.date as appointment_date
                FROM cataract_surgery_readiness_results csrr
                JOIN appointments a ON csrr.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, csrr.created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$patientId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$result) {
                return [
                    'readiness' => null,
                    'status' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            $classification = $result['readiness_classification'] ?? null;
            $status = 'monitor';
            
            if ($classification) {
                $classificationLower = strtolower($classification);
                if (strpos($classificationLower, 'surgery recommended') !== false) {
                    $status = 'surgery_recommended';
                } elseif (strpos($classificationLower, 'consider') !== false) {
                    $status = 'consider_surgery';
                }
            }

            return [
                'readiness' => $classification,
                'status' => $status,
                'message' => $classification ?? 'Not available',
                'appointment_id' => $result['appointment_id'],
                'date' => $result['appointment_date'] ?? null
            ];

        } catch (\Exception $e) {
            return [
                'readiness' => null,
                'status' => null,
                'message' => 'Not available',
                'appointment_id' => null
            ];
        }
    }

    /**
     * Get Dry Eye status from osdi_results
     * 
     * @param int $patientId Patient ID
     * @return array Dry Eye status data
     */
    private function getDryEyeStatus($patientId): array
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'osdi_results'");
            if ($stmt->rowCount() === 0) {
                return [
                    'osdi_score' => null,
                    'severity' => null,
                    'trend' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            // Get last 2 OSDI scores
            $stmt = $this->pdo->prepare("
                SELECT osdi_score, severity, measurement_date, a.id as appointment_id, a.date as appointment_date
                FROM osdi_results osdi
                JOIN appointments a ON osdi.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY measurement_date DESC, osdi.created_at DESC
                LIMIT 2
            ");
            $stmt->execute([$patientId]);
            $osdiData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($osdiData)) {
                return [
                    'osdi_score' => null,
                    'severity' => null,
                    'trend' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            $latest = $osdiData[0];
            $previous = isset($osdiData[1]) ? $osdiData[1] : null;

            // Determine trend
            $trend = 'stable';
            if ($previous && $latest['osdi_score'] !== null && $previous['osdi_score'] !== null) {
                $change = $latest['osdi_score'] - $previous['osdi_score'];
                if ($change <= -3) {
                    $trend = 'improving';
                } elseif ($change >= 3) {
                    $trend = 'worsening';
                }
            }

            return [
                'osdi_score' => $latest['osdi_score'] !== null ? (float)$latest['osdi_score'] : null,
                'severity' => $latest['severity'] ?? null,
                'trend' => $trend,
                'message' => $latest['severity'] ?? 'Not available',
                'appointment_id' => $latest['appointment_id'],
                'date' => $latest['appointment_date'] ?? null
            ];

        } catch (\Exception $e) {
            return [
                'osdi_score' => null,
                'severity' => null,
                'trend' => null,
                'message' => 'Not available',
                'appointment_id' => null
            ];
        }
    }

    /**
     * Get Macular Thickness status from macular_thickness_results
     * 
     * @param int $patientId Patient ID
     * @return array Macular Thickness status data
     */
    private function getMacularThicknessStatus($patientId): array
    {
        try {
            // Check if table exists
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'macular_thickness_results'");
            if ($stmt->rowCount() === 0) {
                return [
                    'latest' => null,
                    'trend' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            // Get last 2 measurements per eye
            $stmt = $this->pdo->prepare("
                SELECT central_thickness, eye, measurement_date, a.id as appointment_id, a.date as appointment_date
                FROM macular_thickness_results mtr
                JOIN appointments a ON mtr.appointment_id = a.id
                WHERE a.patient_id = ?
                ORDER BY measurement_date DESC, mtr.created_at DESC
                LIMIT 4
            ");
            $stmt->execute([$patientId]);
            $thicknessData = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($thicknessData)) {
                return [
                    'latest' => null,
                    'trend' => null,
                    'message' => 'Not available',
                    'appointment_id' => null
                ];
            }

            // Group by eye
            $byEye = ['OD' => [], 'OS' => []];
            foreach ($thicknessData as $row) {
                $eye = strtoupper($row['eye'] ?? 'OD');
                if (isset($byEye[$eye])) {
                    $byEye[$eye][] = $row;
                }
            }

            // Get latest average
            $latestValues = [];
            foreach ($byEye as $eye => $measurements) {
                if (!empty($measurements)) {
                    $latestValues[] = (float)$measurements[0]['central_thickness'];
                }
            }

            $latest = !empty($latestValues) ? array_sum($latestValues) / count($latestValues) : null;

            // Determine trend (worsening if increase > 50µm)
            $trend = 'stable';
            foreach ($byEye as $eye => $measurements) {
                if (count($measurements) >= 2) {
                    $change = (float)$measurements[0]['central_thickness'] - (float)$measurements[1]['central_thickness'];
                    if ($change > 50) {
                        $trend = 'worsening';
                        break;
                    } elseif ($change < -50) {
                        $trend = 'improving';
                    }
                }
            }

            return [
                'latest' => $latest !== null ? round($latest, 0) : null,
                'trend' => $trend,
                'message' => $latest !== null ? round($latest, 0) . ' µm' : 'Not available',
                'appointment_id' => $thicknessData[0]['appointment_id'],
                'date' => $thicknessData[0]['appointment_date'] ?? null
            ];

        } catch (\Exception $e) {
            return [
                'latest' => null,
                'trend' => null,
                'message' => 'Not available',
                'appointment_id' => null
            ];
        }
    }

    /**
     * Generate clinical alerts based on snapshot data
     * 
     * @param array $snapshot Clinical snapshot data
     * @return array Array of alert objects
     */
    private function generateAlerts(array $snapshot): array
    {
        $alerts = [];

        // IOP Above Target alert
        if ($snapshot['iop']['status'] === 'warning' && $snapshot['iop']['target'] !== null) {
            $alerts[] = [
                'type' => 'iop_above_target',
                'severity' => 'warning',
                'message' => "IOP above target ({$snapshot['iop']['value']} mmHg vs target {$snapshot['iop']['target']} mmHg)",
                'appointment_id' => $snapshot['iop']['appointment_id']
            ];
        }

        // Visual Acuity Deterioration alert
        if ($snapshot['visual_acuity']['trend'] === '↑' && 
            $snapshot['visual_acuity']['logmar_last'] !== null && 
            $snapshot['visual_acuity']['logmar_previous'] !== null) {
            $change = $snapshot['visual_acuity']['logmar_last'] - $snapshot['visual_acuity']['logmar_previous'];
            if ($change >= 0.2) {
                $alerts[] = [
                    'type' => 'visual_acuity_deterioration',
                    'severity' => 'warning',
                    'message' => 'Visual acuity deterioration detected',
                    'appointment_id' => $snapshot['visual_acuity']['appointment_id']
                ];
            }
        }

        // Worsening Macular Thickness alert
        if ($snapshot['macular_thickness']['trend'] === 'worsening') {
            $alerts[] = [
                'type' => 'macular_thickness_worsening',
                'severity' => 'critical',
                'message' => 'Worsening macular thickness trend detected',
                'appointment_id' => $snapshot['macular_thickness']['appointment_id']
            ];
        }

        // Severe Dry Eye alert
        if ($snapshot['dry_eye']['severity'] === 'Severe' || 
            ($snapshot['dry_eye']['osdi_score'] !== null && $snapshot['dry_eye']['osdi_score'] >= 33)) {
            $alerts[] = [
                'type' => 'severe_dry_eye',
                'severity' => 'warning',
                'message' => 'Severe dry eye detected (OSDI ≥ 33)',
                'appointment_id' => $snapshot['dry_eye']['appointment_id']
            ];
        }

        return $alerts;
    }

    /**
     * Generate clinical summary paragraph
     * 
     * @param array $snapshot Clinical snapshot data
     * @return string Clinical summary text
     */
    public function generateClinicalSummary(array $snapshot): string
    {
        $summaryParts = [];

        // IOP status
        if ($snapshot['iop']['value'] !== null) {
            $iopText = "IOP: {$snapshot['iop']['value']} mmHg";
            if ($snapshot['iop']['target'] !== null) {
                $iopText .= " (Target: {$snapshot['iop']['target']} mmHg)";
            }
            if ($snapshot['iop']['status'] === 'warning') {
                $iopText .= " - {$snapshot['iop']['message']}";
            }
            $summaryParts[] = $iopText;
        }

        // Visual Acuity status
        if ($snapshot['visual_acuity']['last'] !== null) {
            $vaText = "Visual Acuity: {$snapshot['visual_acuity']['last']}";
            if ($snapshot['visual_acuity']['trend'] !== '→') {
                $vaText .= " ({$snapshot['visual_acuity']['message']})";
            }
            $summaryParts[] = $vaText;
        }

        // Cataract status
        if ($snapshot['cataract']['readiness'] !== null) {
            $summaryParts[] = "Cataract Readiness: {$snapshot['cataract']['readiness']}";
        }

        // Dry Eye status
        if ($snapshot['dry_eye']['osdi_score'] !== null) {
            $dryEyeText = "Dry Eye (OSDI): {$snapshot['dry_eye']['osdi_score']} ({$snapshot['dry_eye']['severity']})";
            if ($snapshot['dry_eye']['trend'] !== 'stable') {
                $dryEyeText .= " - {$snapshot['dry_eye']['trend']}";
            }
            $summaryParts[] = $dryEyeText;
        }

        // Macular Thickness status
        if ($snapshot['macular_thickness']['latest'] !== null) {
            $mtText = "Macular Thickness: {$snapshot['macular_thickness']['latest']} µm";
            if ($snapshot['macular_thickness']['trend'] !== 'stable') {
                $mtText .= " ({$snapshot['macular_thickness']['trend']})";
            }
            $summaryParts[] = $mtText;
        }

        if (empty($summaryParts)) {
            return "Clinical data not available for this patient.";
        }

        return implode('. ', $summaryParts) . '.';
    }
}


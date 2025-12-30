<?php

namespace App\Services;

/**
 * OSDI (Ocular Surface Disease Index) Calculator Service
 * 
 * Calculates OSDI score from standard 12-question questionnaire and classifies dry eye severity.
 * Supports follow-up comparison with previous measurements.
 * 
 * Clinical Logic:
 * - Standard OSDI questionnaire: 12 questions scored 0-4
 * - Formula: OSDI Score = (Sum of scores × 25) / Number of answered questions
 * - Severity Classification:
 *   - 0-12: Normal
 *   - 13-22: Mild
 *   - 23-32: Moderate
 *   - ≥33: Severe
 * - Follow-up Comparison:
 *   - Improvement: Score decrease ≥3 points
 *   - Worsening: Score increase ≥3 points
 *   - No significant change: Change <3 points
 */
class OSDICalculatorService implements CalculatorInterface
{
    /**
     * Standard OSDI Questions
     * 
     * @var array
     */
    private $questions = [
        1 => 'Eyes that are sensitive to light',
        2 => 'Eyes that feel gritty',
        3 => 'Painful or sore eyes',
        4 => 'Blurred vision',
        5 => 'Poor vision',
        6 => 'Reading',
        7 => 'Driving at night',
        8 => 'Working with a computer or bank machine (ATM)',
        9 => 'Watching TV',
        10 => 'Windy conditions',
        11 => 'Places or areas with low humidity (very dry)',
        12 => 'Areas that are air conditioned'
    ];

    /**
     * Calculate OSDI score and severity classification
     * 
     * @param array $data Input data containing:
     *   - questions (array): Array of question scores (1-12), each 0-4
     *   - measurement_date (string): Date of measurement (Y-m-d format)
     *   - previous_score (float): Optional previous OSDI score for comparison
     *   - previous_date (string): Optional previous measurement date
     * 
     * @return array Calculation results
     */
    public function calculate(array $data): array
    {
        $validation = $this->validate($data);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'errors' => $validation['errors']
            ];
        }

        // Extract question answers
        $questionScores = [];
        $answeredCount = 0;
        $totalScore = 0;

        for ($i = 1; $i <= 12; $i++) {
            $questionKey = "question_{$i}";
            $score = isset($data['questions'][$i]) ? (int)$data['questions'][$i] : null;
            
            if ($score !== null && $score >= 0 && $score <= 4) {
                $questionScores[$i] = $score;
                $totalScore += $score;
                $answeredCount++;
            } else {
                $questionScores[$i] = null;
            }
        }

        // Calculate OSDI score
        // Formula: (Sum of scores × 25) / Number of answered questions
        $osdiScore = 0;
        if ($answeredCount > 0) {
            $osdiScore = ($totalScore * 25) / $answeredCount;
            $osdiScore = round($osdiScore, 2);
        }

        // Classify severity
        $severity = $this->classifySeverity($osdiScore);

        // Generate clinical note
        $clinicalNote = $this->generateClinicalNote($osdiScore, $severity, $answeredCount);

        // Follow-up comparison if previous score provided
        $followUpComparison = null;
        if (isset($data['previous_score']) && is_numeric($data['previous_score'])) {
            $previousScore = (float)$data['previous_score'];
            $previousDate = $data['previous_date'] ?? null;
            $followUpComparison = $this->compareWithPrevious($osdiScore, $previousScore, $previousDate);
        }

        return [
            'success' => true,
            'osdi_score' => $osdiScore,
            'severity' => $severity,
            'answered_questions' => $answeredCount,
            'total_questions' => 12,
            'question_scores' => $questionScores,
            'measurement_date' => $data['measurement_date'],
            'clinical_note' => $clinicalNote,
            'follow_up_comparison' => $followUpComparison
        ];
    }

    /**
     * Classify dry eye severity based on OSDI score
     * 
     * @param float $score OSDI score
     * @return string Severity classification
     */
    private function classifySeverity(float $score): string
    {
        if ($score <= 12) {
            return 'Normal';
        } elseif ($score <= 22) {
            return 'Mild';
        } elseif ($score <= 32) {
            return 'Moderate';
        } else {
            return 'Severe';
        }
    }

    /**
     * Compare current score with previous score
     * 
     * @param float $currentScore Current OSDI score
     * @param float $previousScore Previous OSDI score
     * @param string|null $previousDate Previous measurement date
     * @return array Comparison results
     */
    private function compareWithPrevious(float $currentScore, float $previousScore, ?string $previousDate): array
    {
        $scoreChange = $currentScore - $previousScore;
        $absoluteChange = abs($scoreChange);

        // Determine trend
        $trend = 'no_change';
        if ($absoluteChange >= 3) {
            if ($scoreChange < 0) {
                $trend = 'improving';
            } else {
                $trend = 'worsening';
            }
        }

        // Generate comparison note
        $comparisonNote = $this->generateComparisonNote($currentScore, $previousScore, $scoreChange, $trend, $previousDate);

        return [
            'previous_score' => round($previousScore, 2),
            'previous_date' => $previousDate,
            'current_score' => round($currentScore, 2),
            'score_change' => round($scoreChange, 2),
            'absolute_change' => round($absoluteChange, 2),
            'trend' => $trend,
            'comparison_note' => $comparisonNote
        ];
    }

    /**
     * Generate clinical note
     * 
     * @param float $score OSDI score
     * @param string $severity Severity classification
     * @param int $answeredCount Number of answered questions
     * @return string Clinical note
     */
    private function generateClinicalNote(float $score, string $severity, int $answeredCount): string
    {
        $note = sprintf("OSDI score: %.2f. ", $score);
        $note .= sprintf("Severity classification: %s dry eye syndrome. ", $severity);
        
        if ($answeredCount < 12) {
            $note .= sprintf("Note: Only %d out of 12 questions were answered. ", $answeredCount);
        }
        
        $note .= "The OSDI questionnaire assesses dry eye symptoms and their impact on vision-related quality of life.";
        
        return $note;
    }

    /**
     * Generate comparison note for follow-up
     * 
     * @param float $currentScore Current OSDI score
     * @param float $previousScore Previous OSDI score
     * @param float $scoreChange Score change
     * @param string $trend Trend classification
     * @param string|null $previousDate Previous measurement date
     * @return string Comparison note
     */
    private function generateComparisonNote(float $currentScore, float $previousScore, float $scoreChange, string $trend, ?string $previousDate): string
    {
        $dateStr = $previousDate ? date('M d, Y', strtotime($previousDate)) : 'previous visit';
        
        if ($trend === 'improving') {
            return sprintf(
                "OSDI score improved from %.2f (%s) to %.2f (decrease of %.2f points), indicating improvement in dry eye symptoms.",
                $previousScore,
                $dateStr,
                $currentScore,
                abs($scoreChange)
            );
        } elseif ($trend === 'worsening') {
            return sprintf(
                "OSDI score worsened from %.2f (%s) to %.2f (increase of %.2f points), indicating deterioration in dry eye symptoms.",
                $previousScore,
                $dateStr,
                $currentScore,
                abs($scoreChange)
            );
        } else {
            return sprintf(
                "OSDI score remained relatively stable (%.2f vs %.2f from %s), indicating no significant change in dry eye symptoms.",
                $currentScore,
                $previousScore,
                $dateStr
            );
        }
    }

    /**
     * Get standard OSDI questions
     * 
     * @return array Questions array
     */
    public function getQuestions(): array
    {
        return $this->questions;
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

        // Validate questions
        if (!isset($data['questions']) || !is_array($data['questions'])) {
            $errors[] = 'Questions data is required and must be an array';
            return [
                'valid' => false,
                'errors' => $errors
            ];
        }

        $answeredCount = 0;
        for ($i = 1; $i <= 12; $i++) {
            if (isset($data['questions'][$i])) {
                $score = $data['questions'][$i];
                if (!is_numeric($score)) {
                    $errors[] = "Question {$i}: Score must be numeric";
                } elseif ($score < 0 || $score > 4) {
                    $errors[] = "Question {$i}: Score must be between 0 and 4";
                } else {
                    $answeredCount++;
                }
            }
        }

        // Require at least some questions answered
        if ($answeredCount === 0) {
            $errors[] = 'At least one question must be answered';
        }

        // Validate measurement date
        if (!isset($data['measurement_date']) || empty($data['measurement_date'])) {
            $errors[] = 'Measurement date is required';
        } else {
            $date = strtotime($data['measurement_date']);
            if ($date === false) {
                $errors[] = 'Invalid measurement date format. Use Y-m-d format (e.g., 2024-01-15)';
            }
        }

        // Validate previous score if provided
        if (isset($data['previous_score'])) {
            if (!is_numeric($data['previous_score'])) {
                $errors[] = 'Previous score must be numeric';
            } elseif ($data['previous_score'] < 0 || $data['previous_score'] > 100) {
                $errors[] = 'Previous score must be between 0 and 100';
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
        return 'Dry Eye Severity Index (OSDI) Calculator';
    }
}


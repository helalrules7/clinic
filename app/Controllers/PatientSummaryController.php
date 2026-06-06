<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;
use DateTime;
use Throwable;

class PatientSummaryController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * GET /api/patients/:id/summary
     */
    public function summary($patientId)
    {
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $patientId = (int) $patientId;
        if ($patientId <= 0) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => 'Invalid patient id'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        // --- Patient base (required) ---
        // Schema note: real column is `dob` (not `date_of_birth`). We alias it
        // so the rest of this method keeps the readable name.
        try {
            $stmt = $this->pdo->prepare('SELECT id, first_name, last_name, dob AS date_of_birth, gender, phone FROM patients WHERE id = ? LIMIT 1');
            $stmt->execute([$patientId]);
            $patientRow = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('[PatientSummaryController::summary] base query failed for id=' . $patientId . ': ' . $e->getMessage());
            $patientRow = false;
        }

        if (!$patientRow) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Patient not found'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $first = trim((string) ($patientRow['first_name'] ?? ''));
        $last  = trim((string) ($patientRow['last_name'] ?? ''));
        $name  = trim($first . ' ' . $last);

        $dob = $patientRow['date_of_birth'] ?? null;
        $age = null;
        if (!empty($dob) && $dob !== '0000-00-00') {
            try {
                $age = (new DateTime())->diff(new DateTime($dob))->y;
            } catch (Throwable $e) {
                $age = null;
            }
        }

        $patient = [
            'id'     => (int) $patientRow['id'],
            'name'   => $name,
            'age'    => $age,
            'gender' => $patientRow['gender'] ?? null,
            'phone'  => $patientRow['phone'] ?? null,
        ];

        // --- Last visit (best-effort) ---
        $lastVisit = null;
        try {
            // Schema note: appointment.status values are Booked / Completed / NoShow / Closed.
            // "Previous visit" includes any past appointment whose status is NOT a
            // cancellation-style state (Cancelled/Rescheduled aren't present in this
            // schema but the IN-list is the safer query shape). Matching the
            // patients-list table behaviour which counts ALL past appointments.
            $sql = "SELECT date, visit_type
                    FROM appointments
                    WHERE patient_id = ?
                      AND (date < CURDATE() OR (date = CURDATE() AND end_time < NOW()))
                      AND status IN ('Completed', 'NoShow', 'Closed')
                    ORDER BY date DESC, start_time DESC
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$patientId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $lastVisit = [
                    'date' => $row['date'] ?? null,
                    'type' => $row['visit_type'] ?? null,
                ];
            }
        } catch (Throwable $e) {
            $lastVisit = null;
        }

        // --- Next appointment (best-effort) ---
        $nextAppointment = null;
        try {
            $sql = "SELECT date, start_time, status
                    FROM appointments
                    WHERE patient_id = ?
                      AND (date > CURDATE() OR (date = CURDATE() AND start_time > NOW()))
                      AND status != 'Cancelled'
                    ORDER BY date ASC, start_time ASC
                    LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$patientId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($row) {
                $nextAppointment = [
                    'date'   => $row['date'] ?? null,
                    'time'   => $row['start_time'] ?? null,
                    'status' => $row['status'] ?? null,
                ];
            }
        } catch (Throwable $e) {
            $nextAppointment = null;
        }

        // --- Active alerts (best-effort) ---
        $activeAlerts      = [];
        $activeAlertsCount = 0;
        try {
            $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM alerts WHERE patient_id = ? AND is_active = 1');
            $countStmt->execute([$patientId]);
            $activeAlertsCount = (int) $countStmt->fetchColumn();
        } catch (Throwable $e) {
            $activeAlertsCount = 0;
        }

        try {
            $sql = 'SELECT id, message
                    FROM alerts
                    WHERE patient_id = ? AND is_active = 1
                    ORDER BY alert_date DESC
                    LIMIT 3';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$patientId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            foreach ($rows as $r) {
                $msg = (string) ($r['message'] ?? '');
                $title = $msg;
                if (function_exists('mb_strlen') && mb_strlen($title) > 80) {
                    $title = mb_substr($title, 0, 77) . '...';
                } elseif (strlen($title) > 80) {
                    $title = substr($title, 0, 77) . '...';
                }
                $activeAlerts[] = [
                    'id'    => (int) $r['id'],
                    'level' => 'med', // alerts table has no level column yet
                    'title' => $title,
                ];
            }
        } catch (Throwable $e) {
            $activeAlerts = [];
        }

        echo json_encode([
            'success'             => true,
            'patient'             => $patient,
            'last_visit'          => $lastVisit,
            'next_appointment'    => $nextAppointment,
            'active_alerts_count' => $activeAlertsCount,
            'active_alerts'       => $activeAlerts,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}

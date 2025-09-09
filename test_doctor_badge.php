<?php
/**
 * Test Doctor Badge functionality
 */

session_start();

// Simulate admin user in View As mode
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'System Admin',
    'role' => 'doctor' // In View As mode
];
$_SESSION['view_as_mode'] = true;
$_SESSION['view_as_role'] = 'doctor';
$_SESSION['original_role'] = 'admin';

echo "<h1>Doctor Badge Test</h1>";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    // Test database connection
    $pdo = \App\Config\Database::getInstance()->getConnection();
    
    // Test getPatient method with a sample patient
    $stmt = $pdo->prepare("
        SELECT p.*, mh.allergies, mh.medications, mh.systemic_history, mh.ocular_history,
               (SELECT u.name 
                FROM timeline_events te 
                LEFT JOIN users u ON te.actor_user_id = u.id
                WHERE te.patient_id = p.id 
                AND te.event_type = 'Booking' 
                AND te.event_summary LIKE '%New patient registered%' 
                ORDER BY te.created_at ASC 
                LIMIT 1) as created_by_name,
               (SELECT d.display_name 
                FROM timeline_events te 
                LEFT JOIN users u ON te.actor_user_id = u.id
                LEFT JOIN doctors d ON u.id = d.user_id
                WHERE te.patient_id = p.id 
                AND te.event_type = 'Booking' 
                AND te.event_summary LIKE '%New patient registered%' 
                ORDER BY te.created_at ASC 
                LIMIT 1) as created_by_doctor_name
        FROM patients p
        LEFT JOIN medical_history mh ON p.id = mh.patient_id
        WHERE p.id = ?
    ");
    
    // Get first patient
    $stmt->execute([1]);
    $patient = $stmt->fetch();
    
    if ($patient) {
        echo "<h2>Patient Info:</h2>";
        echo "Patient Name: " . htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) . "<br>";
        echo "Created By Name: " . ($patient['created_by_name'] ?? 'Not found') . "<br>";
        echo "Created By Doctor Name: " . ($patient['created_by_doctor_name'] ?? 'Not found') . "<br>";
        
        // Test treating doctor logic
        $treatingDoctor = null;
        if (!empty($patient['created_by_doctor_name'])) {
            $treatingDoctor = [
                'name' => $patient['created_by_name'],
                'display_name' => $patient['created_by_doctor_name']
            ];
        }
        
        echo "<h2>Treating Doctor Info:</h2>";
        if ($treatingDoctor) {
            echo "Name: " . htmlspecialchars($treatingDoctor['name']) . "<br>";
            echo "Display Name: " . htmlspecialchars($treatingDoctor['display_name']) . "<br>";
        } else {
            echo "No treating doctor info found<br>";
        }
        
        // Show badge HTML
        echo "<h2>Badge HTML:</h2>";
        if ($treatingDoctor) {
            echo '<span class="badge doctor-badge fs-6 px-4 py-2">';
            echo '<i class="bi bi-person-badge me-2"></i>';
            echo '<strong>Treating Doctor:</strong> ';
            echo htmlspecialchars($treatingDoctor['display_name'] ?? $treatingDoctor['name']);
            echo '</span>';
        }
        
    } else {
        echo "No patients found in database<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Links:</h2>";
echo "<a href='/doctor/patient/1'>Test Patient Profile</a><br>";
echo "<a href='/admin/stop-view-as'>Exit View As Mode</a><br>";
?>

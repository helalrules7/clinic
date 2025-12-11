<?php
/**
 * Script to update medical history for all patients from existing consultations
 * 
 * This script:
 * 1. Creates a database backup
 * 2. Extracts diagnosis information from all consultation notes
 * 3. Creates medical history entries for patients
 * 4. Avoids duplicates
 * 
 * Usage: php update_medical_history_from_consultations.php
 */

// Load environment variables
function loadEnvironment() {
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
}

loadEnvironment();

// Database configuration
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbName = $_ENV['DB_NAME'] ?? 'hclinic_roaya';
$dbUser = $_ENV['DB_USER'] ?? 'hclinic_roaya';
$dbPass = $_ENV['DB_PASS'] ?? 'Carmen@1230';

// Colors for output
$colors = [
    'reset' => "\033[0m",
    'red' => "\033[31m",
    'green' => "\033[32m",
    'yellow' => "\033[33m",
    'blue' => "\033[34m",
    'cyan' => "\033[36m",
];

function printMessage($message, $color = 'reset') {
    global $colors;
    echo $colors[$color] . $message . $colors['reset'] . "\n";
}

function printHeader($message) {
    printMessage("\n" . str_repeat("=", 60), 'cyan');
    printMessage($message, 'cyan');
    printMessage(str_repeat("=", 60), 'cyan');
}

function printStep($step, $message) {
    printMessage("\n[Step $step] $message", 'blue');
}

try {
    printHeader("Medical History Update Script");
    printMessage("Starting medical history update process...", 'yellow');
    
    // Step 1: Create database backup
    printStep(1, "Creating database backup...");
    
    $backupDir = __DIR__ . '/backups';
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }
    
    $backupFile = $backupDir . '/medical_history_backup_' . date('Y-m-d_His') . '.sql';
    $backupCommand = "mysqldump -h $dbHost -u $dbUser -p'$dbPass' $dbName > $backupFile 2>&1";
    
    exec($backupCommand, $backupOutput, $backupReturnCode);
    
    if ($backupReturnCode !== 0) {
        printMessage("Warning: Database backup may have failed. Output: " . implode("\n", $backupOutput), 'yellow');
        printMessage("Do you want to continue? (yes/no): ", 'yellow');
        $handle = fopen("php://stdin", "r");
        $line = fgets($handle);
        if (trim(strtolower($line)) !== 'yes') {
            printMessage("Aborted by user.", 'red');
            exit(1);
        }
    } else {
        printMessage("✓ Database backup created: $backupFile", 'green');
    }
    
    // Step 2: Connect to database
    printStep(2, "Connecting to database...");
    
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    printMessage("✓ Database connection established", 'green');
    
    // Step 3: Get all consultations with diagnosis
    printStep(3, "Fetching consultations with diagnosis...");
    
    $stmt = $pdo->prepare("
        SELECT 
            cn.*,
            a.patient_id,
            a.date as appointment_date,
            a.doctor_id,
            u.id as created_by_user_id
        FROM consultation_notes cn
        INNER JOIN appointments a ON cn.appointment_id = a.id
        LEFT JOIN users u ON cn.created_by = u.id
        WHERE cn.diagnosis IS NOT NULL 
        AND cn.diagnosis != ''
        AND a.patient_id IS NOT NULL
        ORDER BY cn.created_at ASC
    ");
    
    $stmt->execute();
    $consultations = $stmt->fetchAll();
    
    $totalConsultations = count($consultations);
    printMessage("✓ Found $totalConsultations consultations with diagnosis", 'green');
    
    if ($totalConsultations === 0) {
        printMessage("No consultations found. Exiting.", 'yellow');
        exit(0);
    }
    
    // Step 4: Process consultations and create medical history entries
    printStep(4, "Processing consultations and creating medical history entries...");
    
    $created = 0;
    $skipped = 0;
    $errors = 0;
    $processed = 0;
    
    foreach ($consultations as $consultation) {
        $processed++;
        
        try {
            $patientId = $consultation['patient_id'];
            $diagnosis = trim($consultation['diagnosis']);
            
            if (empty($diagnosis)) {
                $skipped++;
                continue;
            }
            
            // Build notes from consultation data
            $notesParts = [];
            
            if (!empty($consultation['chief_complaint'])) {
                $notesParts[] = "Chief Complaint: " . $consultation['chief_complaint'];
            }
            
            if (!empty($consultation['hx_present_illness'])) {
                $notesParts[] = "History of Present Illness: " . $consultation['hx_present_illness'];
            }
            
            if (!empty($consultation['plan'])) {
                $notesParts[] = "Plan: " . $consultation['plan'];
            }
            
            if (!empty($consultation['systemic_disease'])) {
                $notesParts[] = "Systemic Disease: " . $consultation['systemic_disease'];
            }
            
            if (!empty($consultation['medication'])) {
                $notesParts[] = "Medication: " . $consultation['medication'];
            }
            
            $notes = implode("\n\n", $notesParts);
            
            // Use appointment date as diagnosis date
            $diagnosisDate = $consultation['appointment_date'] ?? date('Y-m-d');
            
            // Determine category based on diagnosis content
            $category = 'general';
            $diagnosisLower = strtolower($diagnosis);
            if (stripos($diagnosisLower, 'allergy') !== false || stripos($diagnosisLower, 'allergic') !== false) {
                $category = 'allergy';
            } elseif (stripos($diagnosisLower, 'surgery') !== false || stripos($diagnosisLower, 'surgical') !== false) {
                $category = 'surgery';
            } elseif (stripos($diagnosisLower, 'medication') !== false || stripos($diagnosisLower, 'drug') !== false) {
                $category = 'medication';
            }
            
            // Check if a similar medical history entry already exists
            $checkStmt = $pdo->prepare("
                SELECT id FROM medical_history_entries 
                WHERE patient_id = ? 
                AND condition_name = ? 
                AND diagnosis_date = ?
                AND (notes LIKE ? OR (notes IS NULL AND ? IS NULL))
                LIMIT 1
            ");
            
            $searchPattern = !empty($notes) ? '%' . substr($notes, 0, 50) . '%' : '%';
            $checkStmt->execute([
                $patientId,
                $diagnosis,
                $diagnosisDate,
                $searchPattern,
                $notes
            ]);
            
            if ($checkStmt->fetch()) {
                $skipped++;
                if ($processed % 50 === 0) {
                    printMessage("  Processed: $processed/$totalConsultations (Created: $created, Skipped: $skipped, Errors: $errors)", 'yellow');
                }
                continue;
            }
            
            // Get user ID for created_by
            $userId = $consultation['created_by_user_id'] ?? $consultation['created_by'] ?? 1;
            
            // Insert medical history entry
            $insertStmt = $pdo->prepare("
                INSERT INTO medical_history_entries 
                (patient_id, condition_name, diagnosis_date, status, notes, category, created_by, created_at) 
                VALUES (?, ?, ?, 'active', ?, ?, ?, ?)
            ");
            
            $result = $insertStmt->execute([
                $patientId,
                $diagnosis,
                $diagnosisDate,
                !empty($notes) ? $notes : null,
                $category,
                $userId,
                $consultation['created_at'] ?? date('Y-m-d H:i:s')
            ]);
            
            if ($result) {
                $created++;
            } else {
                $errors++;
            }
            
            // Progress update every 50 records
            if ($processed % 50 === 0) {
                printMessage("  Processed: $processed/$totalConsultations (Created: $created, Skipped: $skipped, Errors: $errors)", 'yellow');
            }
            
        } catch (\Exception $e) {
            $errors++;
            printMessage("  Error processing consultation ID {$consultation['id']}: " . $e->getMessage(), 'red');
        }
    }
    
    // Step 5: Summary
    printStep(5, "Summary");
    
    printMessage("\n" . str_repeat("-", 60), 'cyan');
    printMessage("Processing Complete!", 'green');
    printMessage(str_repeat("-", 60), 'cyan');
    printMessage("Total Consultations Processed: $processed", 'reset');
    printMessage("Medical History Entries Created: $created", 'green');
    printMessage("Entries Skipped (duplicates): $skipped", 'yellow');
    printMessage("Errors: $errors", $errors > 0 ? 'red' : 'green');
    printMessage("\nBackup Location: $backupFile", 'cyan');
    printMessage("\n✓ Script completed successfully!", 'green');
    
} catch (\Exception $e) {
    printMessage("\n✗ Fatal Error: " . $e->getMessage(), 'red');
    printMessage("Stack Trace:\n" . $e->getTraceAsString(), 'red');
    exit(1);
}


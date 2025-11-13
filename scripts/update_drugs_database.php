#!/usr/bin/env php
<?php
/**
 * CLI Script to Update Drugs Database
 * This script updates the drugs database from drugeye.pharorg.com
 * 
 * Usage: php update_drugs_database.php
 */

// Set timezone
date_default_timezone_set('Africa/Cairo');

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Get script directory
$scriptDir = __DIR__;
$projectRoot = dirname($scriptDir);

// Load environment variables
if (file_exists($projectRoot . '/.env')) {
    $lines = file($projectRoot . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Database connection settings
$dbHost = $_ENV['DB_HOST'] ?? 'localhost';
$dbUser = 'hclinic_drugs';
$dbPass = 'Carmen@1230';
$dbName = 'hclinic_drugs';

// Log file
$logFile = $projectRoot . '/logs/drugs_update_' . date('Y-m-d') . '.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    echo $logEntry;
}

try {
    logMessage("Starting drugs database update...", $logFile);
    
    $excelUrl = 'http://www.drugeye.pharorg.com/drugeyeapp/inner-update-files/drugs.xlsx';
    $tempFile = sys_get_temp_dir() . '/drugs_' . time() . '_' . uniqid() . '.db';
    
    logMessage("Downloading file from: {$excelUrl}", $logFile);
    
    // Download file (SQLite database)
    $ch = curl_init($excelUrl);
    $fp = fopen($tempFile, 'wb');
    
    if (!$fp) {
        throw new Exception('Failed to create temporary file: ' . sys_get_temp_dir());
    }
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $curlResult = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    // Check if download was successful
    if ($curlResult === false || !empty($curlError)) {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        throw new Exception('Failed to download file. cURL Error: ' . $curlError);
    }
    
    if ($httpCode !== 200) {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        throw new Exception('Failed to download file. HTTP Code: ' . $httpCode);
    }
    
    // Verify file exists and has content
    if (!file_exists($tempFile) || filesize($tempFile) === 0) {
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        throw new Exception('Downloaded file is empty or missing. File size: ' . (file_exists($tempFile) ? filesize($tempFile) : 0));
    }
    
    $fileSize = filesize($tempFile);
    logMessage("File downloaded successfully. Size: " . number_format($fileSize) . " bytes", $logFile);
    
    // Try to read as SQLite database using sqlite3 command line tool
    $rows = [];
    
    // Check if sqlite3 command line tool is available
    $sqlite3Cmd = '/usr/bin/sqlite3';
    if (!file_exists($sqlite3Cmd) || !is_executable($sqlite3Cmd)) {
        // Try to find sqlite3 in common locations
        $possiblePaths = ['/usr/bin/sqlite3', '/bin/sqlite3', '/usr/local/bin/sqlite3'];
        $sqlite3Cmd = null;
        foreach ($possiblePaths as $path) {
            if (file_exists($path) && is_executable($path)) {
                $sqlite3Cmd = $path;
                break;
            }
        }
        
        if (empty($sqlite3Cmd)) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            throw new Exception('SQLite3 command line tool is not available. Please install sqlite3: sudo apt-get install sqlite3');
        }
    }
    
    logMessage("Using sqlite3 command: {$sqlite3Cmd}", $logFile);
    
    try {
        // Get table name using sqlite3 command with exec
        $tableQuery = escapeshellarg("SELECT name FROM sqlite_master WHERE type='table' LIMIT 1;");
        $tableOutput = [];
        $returnVar = 0;
        exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " {$tableQuery} 2>&1", $tableOutput, $returnVar);
        $tableName = trim(implode("\n", $tableOutput));
        
        if (empty($tableName) || $returnVar !== 0) {
            throw new Exception('No tables found in SQLite database. Error: ' . implode("\n", $tableOutput));
        }
        
        logMessage("Found table: {$tableName}", $logFile);
        
        // Get column names using sqlite3 command with exec
        $columnQuery = escapeshellarg("PRAGMA table_info(`{$tableName}`);");
        $columnOutput = [];
        exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " {$columnQuery} 2>&1", $columnOutput, $returnVar);
        
        $columnNames = [];
        if ($returnVar === 0 && !empty($columnOutput)) {
            // PRAGMA table_info returns: cid|name|type|notnull|dflt_value|pk
            // We need the second column (name)
            foreach ($columnOutput as $line) {
                $parts = explode('|', $line);
                if (isset($parts[1])) {
                    $columnNames[] = trim($parts[1]);
                }
            }
        }
        
        // Fallback: try to get all columns using SELECT * LIMIT 1 with header
        if (empty($columnNames)) {
            $testQuery = escapeshellarg("SELECT * FROM `{$tableName}` LIMIT 1;");
            $testOutput = [];
            exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " -header -csv {$testQuery} 2>&1", $testOutput, $testReturnVar);
            
            if ($testReturnVar === 0 && !empty($testOutput)) {
                $testLine = $testOutput[0];
                $columnNames = str_getcsv($testLine);
            } else {
                throw new Exception('Failed to read column information from SQLite database. Error: ' . implode("\n", $columnOutput));
            }
        }
        
        logMessage("Found " . count($columnNames) . " columns", $logFile);
        
        // Map SQLite columns to database columns
        $columnMap = [];
        foreach ($columnNames as $index => $colName) {
            $colNameUpper = strtoupper(trim($colName));
            if ($colNameUpper === 'ID' || $colNameUpper === 'CID') {
                $columnMap['ID'] = $colName;
            } elseif ($colNameUpper === 'FIRSTNAME' || $colNameUpper === 'NAME') {
                $columnMap['FirstName'] = $colName;
            } elseif ($colNameUpper === 'LASTNAME') {
                $columnMap['LastName'] = $colName;
            } elseif ($colNameUpper === 'PRICE') {
                $columnMap['price'] = $colName;
            } elseif ($colNameUpper === 'PRICEOLD') {
                $columnMap['priceold'] = $colName;
            } elseif ($colNameUpper === 'IMAGEID') {
                $columnMap['imageid'] = $colName;
            } elseif ($colNameUpper === 'COMPANY') {
                $columnMap['Company'] = $colName;
            } elseif ($colNameUpper === 'PHARMACOLOGY') {
                $columnMap['Pharmacology'] = $colName;
            } elseif ($colNameUpper === 'SRDE') {
                $columnMap['SRDE'] = $colName;
            } elseif ($colNameUpper === 'GI') {
                $columnMap['GI'] = $colName;
            } elseif ($colNameUpper === 'ROUTE') {
                $columnMap['Route'] = $colName;
            }
        }
        
        // Build SELECT query with column mapping
        $selectColumns = [];
        $selectColumns[] = isset($columnMap['ID']) ? $columnMap['ID'] . ' as ID' : 'NULL as ID';
        $selectColumns[] = isset($columnMap['FirstName']) ? $columnMap['FirstName'] . ' as FirstName' : 'NULL as FirstName';
        $selectColumns[] = isset($columnMap['LastName']) ? $columnMap['LastName'] . ' as LastName' : 'NULL as LastName';
        $selectColumns[] = isset($columnMap['price']) ? $columnMap['price'] . ' as price' : 'NULL as price';
        $selectColumns[] = isset($columnMap['priceold']) ? $columnMap['priceold'] . ' as priceold' : 'NULL as priceold';
        $selectColumns[] = isset($columnMap['imageid']) ? $columnMap['imageid'] . ' as imageid' : 'NULL as imageid';
        $selectColumns[] = isset($columnMap['Company']) ? $columnMap['Company'] . ' as Company' : 'NULL as Company';
        $selectColumns[] = isset($columnMap['Pharmacology']) ? $columnMap['Pharmacology'] . ' as Pharmacology' : 'NULL as Pharmacology';
        $selectColumns[] = isset($columnMap['SRDE']) ? $columnMap['SRDE'] . ' as SRDE' : 'NULL as SRDE';
        $selectColumns[] = isset($columnMap['GI']) ? $columnMap['GI'] . ' as GI' : 'NULL as GI';
        $selectColumns[] = isset($columnMap['Route']) ? $columnMap['Route'] . ' as Route' : 'NULL as Route';
        
        $selectQuery = "SELECT " . implode(', ', $selectColumns) . " FROM `{$tableName}`;";
        
        // Export data to CSV using sqlite3 command with exec
        $csvFile = sys_get_temp_dir() . '/drugs_export_' . time() . '_' . uniqid() . '.csv';
        $exportQuery = escapeshellarg($selectQuery);
        $exportOutput = [];
        $returnVar = 0;
        
        logMessage("Exporting data to CSV...", $logFile);
        
        // Use exec to get output directly, then write to file
        exec("{$sqlite3Cmd} " . escapeshellarg($tempFile) . " -header -csv {$exportQuery} 2>&1", $exportOutput, $returnVar);
        
        if ($returnVar !== 0 || empty($exportOutput)) {
            $errorMsg = !empty($exportOutput) ? implode("\n", $exportOutput) : 'Unknown error';
            throw new Exception('Failed to export data from SQLite database. Return code: ' . $returnVar . '. Error: ' . $errorMsg);
        }
        
        // Write output to CSV file
        $csvContent = implode("\n", $exportOutput);
        if (file_put_contents($csvFile, $csvContent) === false) {
            throw new Exception('Failed to write CSV file');
        }
        
        if (!file_exists($csvFile) || filesize($csvFile) === 0) {
            throw new Exception('CSV file is empty or missing after export');
        }
        
        logMessage("CSV file created. Size: " . number_format(filesize($csvFile)) . " bytes", $logFile);
        
        // Read CSV file
        $csvHandle = fopen($csvFile, 'r');
        if (!$csvHandle) {
            throw new Exception('Failed to open exported CSV file');
        }
        
        // Read header row
        $header = fgetcsv($csvHandle);
        if ($header === false) {
            fclose($csvHandle);
            unlink($csvFile);
            throw new Exception('Failed to read CSV header');
        }
        
        // Map header columns to indices
        $headerMap = [];
        foreach ($header as $index => $colName) {
            $headerMap[trim($colName)] = $index;
        }
        
        // Read data rows
        $mappedRows = [];
        while (($csvRow = fgetcsv($csvHandle)) !== false) {
            $mappedRow = [
                isset($headerMap['ID']) && isset($csvRow[$headerMap['ID']]) ? trim($csvRow[$headerMap['ID']]) : null,
                isset($headerMap['FirstName']) && isset($csvRow[$headerMap['FirstName']]) ? trim($csvRow[$headerMap['FirstName']]) : null,
                isset($headerMap['LastName']) && isset($csvRow[$headerMap['LastName']]) ? trim($csvRow[$headerMap['LastName']]) : null,
                isset($headerMap['price']) && isset($csvRow[$headerMap['price']]) ? trim($csvRow[$headerMap['price']]) : null,
                isset($headerMap['priceold']) && isset($csvRow[$headerMap['priceold']]) ? trim($csvRow[$headerMap['priceold']]) : null,
                isset($headerMap['imageid']) && isset($csvRow[$headerMap['imageid']]) ? trim($csvRow[$headerMap['imageid']]) : null,
                isset($headerMap['Company']) && isset($csvRow[$headerMap['Company']]) ? trim($csvRow[$headerMap['Company']]) : null,
                isset($headerMap['Pharmacology']) && isset($csvRow[$headerMap['Pharmacology']]) ? trim($csvRow[$headerMap['Pharmacology']]) : null,
                isset($headerMap['SRDE']) && isset($csvRow[$headerMap['SRDE']]) ? trim($csvRow[$headerMap['SRDE']]) : null,
                isset($headerMap['GI']) && isset($csvRow[$headerMap['GI']]) ? trim($csvRow[$headerMap['GI']]) : null,
                isset($headerMap['Route']) && isset($csvRow[$headerMap['Route']]) ? trim($csvRow[$headerMap['Route']]) : null
            ];
            $mappedRows[] = $mappedRow;
        }
        
        fclose($csvHandle);
        
        // Clean up CSV file
        if (file_exists($csvFile)) {
            unlink($csvFile);
        }
        
        $rows = $mappedRows;
        
    } catch (Exception $e) {
        $fileSize = file_exists($tempFile) ? filesize($tempFile) : 0;
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        if (isset($csvFile) && file_exists($csvFile)) {
            unlink($csvFile);
        }
        logMessage("SQLite error: " . $e->getMessage(), $logFile);
        logMessage("File size: " . $fileSize . " bytes", $logFile);
        throw $e;
    }
    
    // Remove header row if exists
    if (!empty($rows) && isset($rows[0])) {
        $firstRow = $rows[0];
        if (is_array($firstRow) && (
            (isset($firstRow[0]) && strtoupper(trim((string)$firstRow[0])) === 'ID') ||
            (isset($firstRow[1]) && strtoupper(trim((string)$firstRow[1])) === 'FIRSTNAME')
        )) {
            array_shift($rows);
        }
    }
    
    logMessage("Total rows to process: " . count($rows), $logFile);
    
    // Connect to drug database
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);
    
    logMessage("Connected to database: {$dbName}", $logFile);
    
    // Start transaction
    $pdo->beginTransaction();
    
    $inserted = 0;
    $updated = 0;
    $total = count($rows);
    
    try {
        // Clear existing data
        logMessage("Clearing existing data...", $logFile);
        $pdo->exec("DELETE FROM drugs");
        
        // Prepare insert statement
        $stmt = $pdo->prepare("
            INSERT INTO drugs 
            (ID, FirstName, LastName, price, priceold, imageid, Company, Pharmacology, SRDE, GI, Route)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        logMessage("Inserting data...", $logFile);
        
        // Process each row
        foreach ($rows as $rowIndex => $row) {
            // Skip empty rows
            if (empty($row) || (!isset($row[0]) && !isset($row[1]) && !isset($row[2]))) {
                continue;
            }
            
            // Map CSV columns to database columns
            $id = isset($row[0]) ? trim((string)$row[0]) : null;
            $firstName = isset($row[1]) ? trim((string)$row[1]) : '';
            $lastName = isset($row[2]) ? trim((string)$row[2]) : '';
            $price = isset($row[3]) ? trim((string)$row[3]) : '';
            $priceold = isset($row[4]) ? trim((string)$row[4]) : '';
            $imageid = isset($row[5]) ? trim((string)$row[5]) : '';
            $company = isset($row[6]) ? trim((string)$row[6]) : '';
            $pharmacology = isset($row[7]) ? trim((string)$row[7]) : '';
            $srde = isset($row[8]) ? trim((string)$row[8]) : '';
            $gi = isset($row[9]) ? trim((string)$row[9]) : '';
            $route = isset($row[10]) ? trim((string)$row[10]) : '';
            
            // Skip if no ID or drug name
            if (empty($id) && empty($firstName) && empty($lastName)) {
                continue;
            }
            
            // Convert ID to integer if possible, otherwise skip
            if (!is_numeric($id) || empty($id)) {
                if (($rowIndex + 1) % 1000 === 0) {
                    logMessage("Processing row " . ($rowIndex + 1) . " of {$total}...", $logFile);
                }
                continue;
            }
            $id = (int)$id;
            
            // Limit string lengths to match database schema
            $firstName = mb_substr($firstName, 0, 86);
            $lastName = mb_substr($lastName, 0, 100);
            $price = mb_substr($price, 0, 100);
            $priceold = mb_substr($priceold, 0, 100);
            $imageid = mb_substr($imageid, 0, 30);
            $company = mb_substr($company, 0, 54);
            $pharmacology = mb_substr($pharmacology, 0, 96);
            $srde = mb_substr($srde, 0, 60);
            $gi = mb_substr($gi, 0, 1000);
            $route = mb_substr($route, 0, 100);
            
            try {
                $stmt->execute([
                    $id,
                    $firstName ?: null,
                    $lastName ?: null,
                    $price ?: null,
                    $priceold ?: null,
                    $imageid ?: null,
                    $company ?: null,
                    $pharmacology ?: null,
                    $srde ?: null,
                    $gi ?: null,
                    $route ?: null
                ]);
                $inserted++;
                
                if ($inserted % 1000 === 0) {
                    logMessage("Inserted {$inserted} records...", $logFile);
                }
            } catch (PDOException $e) {
                logMessage("Error inserting drug row " . ($rowIndex + 1) . ": " . $e->getMessage(), $logFile);
                // Continue with next row
            }
        }
        
        // Commit transaction
        $pdo->commit();
        logMessage("Transaction committed successfully", $logFile);
        
    } catch (Exception $e) {
        // Rollback transaction if started
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
            logMessage("Transaction rolled back due to error", $logFile);
        }
        throw $e;
    }
    
    // Clean up temp file
    if (file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    logMessage("Database updated successfully!", $logFile);
    logMessage("Statistics: Total={$total}, Inserted={$inserted}, Updated={$updated}", $logFile);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database updated successfully',
        'statistics' => [
            'total' => $total,
            'inserted' => $inserted,
            'updated' => $updated
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
    exit(0);
    
} catch (Exception $e) {
    // Rollback transaction if started
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Clean up temp file
    if (isset($tempFile) && file_exists($tempFile)) {
        unlink($tempFile);
    }
    
    if (isset($csvFile) && file_exists($csvFile)) {
        unlink($csvFile);
    }
    
    logMessage("Error in updateDrugsDatabase: " . $e->getMessage(), $logFile);
    logMessage("Stack trace: " . $e->getTraceAsString(), $logFile);
    
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update database',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
    
    exit(1);
}


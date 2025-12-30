<?php

require_once __DIR__ . '/src/Database.php';

use App\Database;

$pdo = Database::getConnection();

// Read content files
$contentEn = file_get_contents(__DIR__ . '/content/getting-started-en.html');
$contentAr = file_get_contents(__DIR__ . '/content/getting-started-ar.html');

// Escape single quotes for SQL
$contentEn = str_replace("'", "''", $contentEn);
$contentAr = str_replace("'", "''", $contentAr);

try {
    $pdo->beginTransaction();

    // Update English translation
    $stmt = $pdo->prepare("
        UPDATE translations 
        SET content = :content 
        WHERE item_id = 1 AND locale = 'en'
    ");
    $stmt->execute([':content' => $contentEn]);

    // Update Arabic translation
    $stmt = $pdo->prepare("
        UPDATE translations 
        SET content = :content 
        WHERE item_id = 1 AND locale = 'ar'
    ");
    $stmt->execute([':content' => $contentAr]);

    $pdo->commit();
    
    echo "✓ Successfully updated Getting Started content!\n";
    echo "  - English content updated\n";
    echo "  - Arabic content updated\n";
    
} catch (\Exception $e) {
    $pdo->rollBack();
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

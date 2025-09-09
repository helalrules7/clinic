<?php
/**
 * Debug View As functionality
 */

session_start();

echo "<h1>View As Debug</h1>";

// Simulate admin user
$_SESSION['user_id'] = 1;
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Admin User',
    'role' => 'admin'
];

echo "<h2>Before View As:</h2>";
echo "User Role: " . $_SESSION['user']['role'] . "<br>";
echo "View As Mode: " . (isset($_SESSION['view_as_mode']) ? 'true' : 'false') . "<br>";

// Start View As mode
$_SESSION['view_as_mode'] = true;
$_SESSION['view_as_role'] = 'doctor';
$_SESSION['original_role'] = 'admin';
$_SESSION['user']['role'] = 'doctor';

echo "<h2>After View As (Doctor):</h2>";
echo "User Role: " . $_SESSION['user']['role'] . "<br>";
echo "View As Mode: " . ($_SESSION['view_as_mode'] ? 'true' : 'false') . "<br>";
echo "View As Role: " . $_SESSION['view_as_role'] . "<br>";
echo "Original Role: " . $_SESSION['original_role'] . "<br>";

// Test Auth class
try {
    require_once __DIR__ . '/vendor/autoload.php';
    $auth = new \App\Lib\Auth();
    
    echo "<h2>Auth Test:</h2>";
    echo "isViewAsMode(): " . ($auth->isViewAsMode() ? 'true' : 'false') . "<br>";
    echo "getCurrentRole(): " . ($auth->getCurrentRole() ?? 'null') . "<br>";
    echo "getOriginalRole(): " . ($auth->getOriginalRole() ?? 'null') . "<br>";
    
    // Test requireRole
    echo "<h2>Role Check Test:</h2>";
    try {
        $auth->requireRole(['admin']);
        echo "✅ Admin role check: PASSED<br>";
    } catch (Exception $e) {
        echo "❌ Admin role check: FAILED - " . $e->getMessage() . "<br>";
    }
    
    try {
        $auth->requireRole(['doctor']);
        echo "✅ Doctor role check: PASSED<br>";
    } catch (Exception $e) {
        echo "❌ Doctor role check: FAILED - " . $e->getMessage() . "<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Test Links:</h2>";
echo "<a href='/admin/stop-view-as'>Test Stop View As</a><br>";
echo "<a href='/admin/dashboard'>Test Admin Dashboard</a><br>";
echo "<a href='/doctor/dashboard'>Test Doctor Dashboard</a><br>";
?>

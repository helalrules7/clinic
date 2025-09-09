<?php
/**
 * Test View As functionality
 * This file helps test if View As mode is working correctly
 */

session_start();

echo "<h1>View As Test</h1>";

echo "<h2>Session Variables:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>View As Status:</h2>";
echo "view_as_mode: " . (isset($_SESSION['view_as_mode']) ? ($_SESSION['view_as_mode'] ? 'true' : 'false') : 'not set') . "<br>";
echo "view_as_role: " . ($_SESSION['view_as_role'] ?? 'not set') . "<br>";
echo "original_role: " . ($_SESSION['original_role'] ?? 'not set') . "<br>";

echo "<h2>Test Links:</h2>";
echo "<a href='/admin/view-as?role=doctor'>Start View As Doctor</a><br>";
echo "<a href='/admin/view-as?role=secretary'>Start View As Secretary</a><br>";
echo "<a href='/admin/stop-view-as'>Stop View As</a><br>";

echo "<h2>Current User Role:</h2>";
if (isset($_SESSION['user']['role'])) {
    echo "User Role: " . $_SESSION['user']['role'] . "<br>";
} else {
    echo "User role not set in session<br>";
}

echo "<h2>Auth Test:</h2>";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    $auth = new \App\Lib\Auth();
    echo "Auth object created successfully<br>";
    echo "isViewAsMode(): " . ($auth->isViewAsMode() ? 'true' : 'false') . "<br>";
    echo "getCurrentRole(): " . ($auth->getCurrentRole() ?? 'null') . "<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>

<?php
/**
 * Test script for API endpoints
 * Run this to test the new drug-related API endpoints
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API Endpoints Test</h1>";

// Test getMostUsedDrugs endpoint
echo "<h2>Testing /api/getMostUsedDrugs</h2>";
$url = 'http://localhost/clinic/public/api/getMostUsedDrugs?limit=5';
echo "<p>URL: $url</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

// Test searchDrugsAutocomplete endpoint
echo "<h2>Testing /api/searchDrugsAutocomplete</h2>";
$url = 'http://localhost/clinic/public/api/searchDrugsAutocomplete?q=aspirin&limit=5';
echo "<p>URL: $url</p>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p>HTTP Code: $httpCode</p>";
echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

echo "<h2>Test Complete</h2>";
?>

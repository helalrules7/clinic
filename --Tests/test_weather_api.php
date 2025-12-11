<?php
/**
 * Debug script to test Weather API (Open-Meteo and OpenWeatherMap)
 * This file helps diagnose issues with weather API fetching
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Weather API Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #fafafa; }
        .test-section h2 { margin-top: 0; color: #0066cc; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: #666; font-size: 0.9em; margin: 5px 0; }
        .weather-data { background: #e8f4f8; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .weather-data h3 { margin-top: 0; }
        .weather-item { margin: 8px 0; padding: 5px; background: white; border-left: 3px solid #0066cc; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .stats { background: #e8f4f8; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .calculation { background: #fff3cd; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🌤️ Weather API Debug</h1>
        <p>Testing weather API fetching and data processing...</p>
";

// Test coordinates
$testLocations = [
    [
        'name' => 'Kafr El Sheikh (Default)',
        'lat' => 31.1117,
        'lon' => 30.9397
    ],
    [
        'name' => 'Cairo',
        'lat' => 30.0444,
        'lon' => 31.2357
    ]
];

foreach ($testLocations as $location) {
    echo "<div class='test-section'>";
    echo "<h2>📍 Testing: {$location['name']}</h2>";
    echo "<div class='info'>Coordinates: {$location['lat']}, {$location['lon']}</div>";
    
    // Test Open-Meteo API
    echo "<h3>1. Open-Meteo API Test</h3>";
    $openMeteoData = testOpenMeteoAPI($location['lat'], $location['lon']);
    
    if ($openMeteoData) {
        echo "<div class='success'>✅ Open-Meteo API: Success</div>";
        displayWeatherData($openMeteoData, 'Open-Meteo');
        calculateIndices($openMeteoData);
    } else {
        echo "<div class='error'>❌ Open-Meteo API: Failed</div>";
    }
    
    // Test OpenWeatherMap API
    echo "<h3>2. OpenWeatherMap API Test</h3>";
    $openWeatherData = testOpenWeatherMapAPI($location['lat'], $location['lon']);
    
    if ($openWeatherData) {
        echo "<div class='success'>✅ OpenWeatherMap API: Success</div>";
        displayWeatherData($openWeatherData, 'OpenWeatherMap');
        calculateIndices($openWeatherData);
    } else {
        echo "<div class='error'>❌ OpenWeatherMap API: Failed</div>";
    }
    
    echo "</div>";
}

// Functions
function testOpenMeteoAPI($lat, $lon) {
    try {
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m,uv_index,is_day&timezone=auto";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; WeatherApp)'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response || $curlError) {
            echo "<div class='error'>HTTP Error: {$httpCode}, cURL Error: {$curlError}</div>";
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['current'])) {
            echo "<div class='error'>Invalid response structure</div>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
            return null;
        }
        
        $current = $data['current'];
        
        // Map weather code
        $weatherCode = $current['weather_code'] ?? 0;
        $condition = mapWeatherCodeToCondition($weatherCode);
        $icon = getWeatherIconFromCode($weatherCode, $current['is_day'] ?? 1);
        
        // Get location name
        $locationName = getLocationNameFromCoordinates($lat, $lon);
        
        return [
            'temperature' => round($current['temperature_2m'] ?? 20),
            'humidity' => round($current['relative_humidity_2m'] ?? 50),
            'condition' => $condition,
            'icon' => $icon,
            'windSpeed' => round($current['wind_speed_10m'] ?? 0), // Already in km/h
            'location' => $locationName,
            'uvIndex' => round($current['uv_index'] ?? 5),
            'feelsLike' => round($current['temperature_2m'] ?? 20),
            'pressure' => 1013,
            'visibility' => 10,
            'clouds' => estimateCloudsFromWeatherCode($weatherCode),
            'source' => 'Open-Meteo',
            'raw_data' => $current
        ];
        
    } catch (\Exception $e) {
        echo "<div class='error'>Exception: " . $e->getMessage() . "</div>";
        return null;
    }
}

function testOpenWeatherMapAPI($lat, $lon) {
    try {
        $apiKey = '4d8fb5b93d4af21d66a2948710284366';
        $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$response || $curlError) {
            echo "<div class='error'>HTTP Error: {$httpCode}, cURL Error: {$curlError}</div>";
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (!$data || !isset($data['main'])) {
            echo "<div class='error'>Invalid response structure</div>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
            return null;
        }
        
        // Estimate UV index
        $clouds = $data['clouds']['all'] ?? 0;
        $hour = (int)date('H');
        $baseUV = 8;
        if ($hour < 7 || $hour > 18) {
            $uvIndex = 0;
        } elseif ($hour < 10 || $hour > 16) {
            $uvIndex = round($baseUV * 0.5);
        } elseif ($hour < 11 || $hour > 15) {
            $uvIndex = round($baseUV * 0.8);
        } else {
            $cloudFactor = 1 - ($clouds / 100) * 0.7;
            $uvIndex = round($baseUV * $cloudFactor);
        }
        
        return [
            'temperature' => round($data['main']['temp'] ?? 20),
            'humidity' => $data['main']['humidity'] ?? 50,
            'condition' => ucfirst($data['weather'][0]['description'] ?? 'Clear'),
            'icon' => $data['weather'][0]['icon'] ?? '01d',
            'windSpeed' => round(($data['wind']['speed'] ?? 0) * 3.6), // Convert m/s to km/h
            'location' => $data['name'] ?? 'Unknown',
            'uvIndex' => $uvIndex,
            'feelsLike' => round($data['main']['feels_like'] ?? $data['main']['temp']),
            'pressure' => $data['main']['pressure'] ?? 1013,
            'visibility' => round(($data['visibility'] ?? 10000) / 1000),
            'clouds' => $clouds,
            'source' => 'OpenWeatherMap',
            'raw_data' => $data
        ];
        
    } catch (\Exception $e) {
        echo "<div class='error'>Exception: " . $e->getMessage() . "</div>";
        return null;
    }
}

function mapWeatherCodeToCondition($code) {
    $codes = [
        0 => 'Clear',
        1 => 'Mainly Clear',
        2 => 'Partly Cloudy',
        3 => 'Overcast',
        45 => 'Foggy',
        48 => 'Depositing Rime Fog',
        51 => 'Light Drizzle',
        53 => 'Moderate Drizzle',
        55 => 'Dense Drizzle',
        56 => 'Light Freezing Drizzle',
        57 => 'Dense Freezing Drizzle',
        61 => 'Slight Rain',
        63 => 'Moderate Rain',
        65 => 'Heavy Rain',
        66 => 'Light Freezing Rain',
        67 => 'Heavy Freezing Rain',
        71 => 'Slight Snow',
        73 => 'Moderate Snow',
        75 => 'Heavy Snow',
        77 => 'Snow Grains',
        80 => 'Slight Rain Showers',
        81 => 'Moderate Rain Showers',
        82 => 'Violent Rain Showers',
        85 => 'Slight Snow Showers',
        86 => 'Heavy Snow Showers',
        95 => 'Thunderstorm',
        96 => 'Thunderstorm with Hail',
        99 => 'Thunderstorm with Heavy Hail'
    ];
    return $codes[$code] ?? 'Clear';
}

function getWeatherIconFromCode($code, $isDay = 1) {
    if ($code == 0) return $isDay ? '01d' : '01n';
    if ($code <= 2) return $isDay ? '02d' : '02n';
    if ($code == 3) return '04d';
    if ($code >= 45 && $code <= 48) return '50d';
    if ($code >= 51 && $code <= 67) return '09d';
    if ($code >= 71 && $code <= 77) return '13d';
    if ($code >= 80 && $code <= 82) return '09d';
    if ($code >= 85 && $code <= 86) return '13d';
    if ($code >= 95) return '11d';
    return '01d';
}

function estimateCloudsFromWeatherCode($code) {
    if ($code == 0) return 0;
    if ($code <= 2) return 25;
    if ($code == 3) return 100;
    if ($code >= 45 && $code <= 48) return 50;
    if ($code >= 51 && $code <= 67) return 80;
    if ($code >= 71 && $code <= 77) return 90;
    if ($code >= 80 && $code <= 82) return 85;
    if ($code >= 85 && $code <= 86) return 95;
    if ($code >= 95) return 100;
    return 50;
}

function getLocationNameFromCoordinates($lat, $lon) {
    try {
        // Try Nominatim (OpenStreetMap) for reverse geocoding
        $nominatimUrl = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$lat}&lon={$lon}&zoom=10&addressdetails=1";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $nominatimUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; WeatherApp)'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['address'])) {
                $address = $data['address'];
                if (isset($address['city'])) {
                    return $address['city'];
                } elseif (isset($address['town'])) {
                    return $address['town'];
                } elseif (isset($address['village'])) {
                    return $address['village'];
                } elseif (isset($address['municipality'])) {
                    return $address['municipality'];
                }
            }
        }
    } catch (\Exception $e) {
        // Silently fail
    }
    
    // Default based on coordinates
    if (abs($lat - 31.1117) < 1 && abs($lon - 30.9397) < 1) {
        return 'Kafr El Sheikh, Egypt';
    }
    if (abs($lat - 30.0444) < 1 && abs($lon - 31.2357) < 1) {
        return 'Cairo, Egypt';
    }
    
    return sprintf('Location (%.2f, %.2f)', $lat, $lon);
}

function displayWeatherData($weatherData, $source) {
    echo "<div class='weather-data'>";
    echo "<h3>📊 Weather Data from {$source}</h3>";
    
    $items = [
        'Temperature' => $weatherData['temperature'] . '°C',
        'Humidity' => $weatherData['humidity'] . '%',
        'Condition' => $weatherData['condition'],
        'Wind Speed' => $weatherData['windSpeed'] . ' km/h',
        'UV Index' => $weatherData['uvIndex'],
        'Location' => $weatherData['location'],
        'Feels Like' => $weatherData['feelsLike'] . '°C',
        'Pressure' => $weatherData['pressure'] . ' hPa',
        'Visibility' => $weatherData['visibility'] . ' km',
        'Clouds' => $weatherData['clouds'] . '%'
    ];
    
    foreach ($items as $label => $value) {
        echo "<div class='weather-item'><strong>{$label}:</strong> {$value}</div>";
    }
    
    echo "</div>";
}

function calculateIndices($weatherData) {
    // Calculate Pollen Index
    $pollenIndex = calculatePollenIndex($weatherData);
    $pollenLevel = getLevelText($pollenIndex);
    $pollenClass = getLevelClass($pollenIndex);
    
    // Calculate Dry Eye Risk
    $dryEyeRisk = calculateDryEyeRisk($weatherData);
    $dryEyeLevel = getLevelText($dryEyeRisk);
    $dryEyeClass = getLevelClass($dryEyeRisk);
    
    echo "<div class='calculation'>";
    echo "<h3>🧮 Health Indices Calculation</h3>";
    
    echo "<div class='weather-item'>";
    echo "<strong>🌿 Pollen Index:</strong> {$pollenIndex}% ({$pollenLevel}) - Class: {$pollenClass}";
    echo "</div>";
    
    echo "<div class='weather-item'>";
    echo "<strong>👁️ Dry Eye Risk:</strong> {$dryEyeRisk}% ({$dryEyeLevel}) - Class: {$dryEyeClass}";
    echo "</div>";
    
    echo "</div>";
}

function calculatePollenIndex($weatherData) {
    $pollenScore = 50; // Base score
    
    $temp = $weatherData['temperature'] ?? 20;
    $humidity = $weatherData['humidity'] ?? 50;
    $windSpeed = $weatherData['windSpeed'] ?? 10;
    $isRaining = strpos(strtolower($weatherData['condition'] ?? ''), 'rain') !== false;
    
    // Temperature factor
    if ($temp >= 15 && $temp <= 25) {
        $pollenScore += 20;
    } elseif ($temp > 25 && $temp <= 30) {
        $pollenScore += 10;
    } elseif ($temp < 10 || $temp > 35) {
        $pollenScore -= 20;
    }
    
    // Humidity factor
    if ($humidity < 40) {
        $pollenScore += 15;
    } elseif ($humidity > 70) {
        $pollenScore -= 20;
    }
    
    // Wind factor
    if ($windSpeed > 20) {
        $pollenScore += 25;
    } elseif ($windSpeed > 10) {
        $pollenScore += 10;
    }
    
    // Rain reduces pollen
    if ($isRaining) {
        $pollenScore -= 30;
    }
    
    return max(0, min(100, round($pollenScore)));
}

function calculateDryEyeRisk($weatherData) {
    $riskScore = 30; // Base score
    
    $temp = $weatherData['temperature'] ?? 20;
    $humidity = $weatherData['humidity'] ?? 50;
    $windSpeed = $weatherData['windSpeed'] ?? 10;
    $uvIndex = $weatherData['uvIndex'] ?? 5;
    
    // Low humidity increases risk
    if ($humidity < 30) {
        $riskScore += 35;
    } elseif ($humidity < 45) {
        $riskScore += 20;
    } elseif ($humidity > 60) {
        $riskScore -= 15;
    }
    
    // High temperature with low humidity
    if ($temp > 30 && $humidity < 50) {
        $riskScore += 15;
    }
    
    // Wind increases evaporation
    if ($windSpeed > 20) {
        $riskScore += 20;
    } elseif ($windSpeed > 10) {
        $riskScore += 10;
    }
    
    // High UV exposure
    if ($uvIndex > 7) {
        $riskScore += 15;
    } elseif ($uvIndex > 5) {
        $riskScore += 8;
    }
    
    return max(0, min(100, round($riskScore)));
}

function getLevelClass($score) {
    if ($score <= 25) return 'low';
    if ($score <= 50) return 'moderate';
    if ($score <= 75) return 'high';
    return 'very-high';
}

function getLevelText($score) {
    if ($score <= 25) return 'Low';
    if ($score <= 50) return 'Moderate';
    if ($score <= 75) return 'High';
    return 'Very High';
}

echo "</div></body></html>";
?>

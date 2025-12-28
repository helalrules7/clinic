<?php

use App\Router;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

// DEBUG BLOCK
// echo "<pre>";
// print_r($_SERVER);
// echo "</pre>";
// die();

$router = new Router();

// Views
$router->get('/', ['App\Controllers\HomeController', 'index']);
$router->get('/public/', ['App\Controllers\HomeController', 'index']);
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index']);

// API
$router->post('/api/posts', ['App\Controllers\ApiController', 'store']);
$router->get('/api/search', ['App\Controllers\ApiController', 'search']);
$router->post('/api/delete', ['App\Controllers\ApiController', 'delete']);

$router->dispatch();

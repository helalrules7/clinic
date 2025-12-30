<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\Router;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$router = new Router();

// Views
$router->get('/', ['App\Controllers\HomeController', 'index']);
$router->get('/dashboard', ['App\Controllers\DashboardController', 'index']);

// API
$router->post('/api/posts', ['App\Controllers\ApiController', 'store']);
$router->get('/api/search', ['App\Controllers\ApiController', 'search']);
$router->post('/api/delete', ['App\Controllers\ApiController', 'delete']);

$router->dispatch();

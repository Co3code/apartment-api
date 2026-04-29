<?php

declare(strict_types=1);

$base = 'C:\\xampp\\htdocs\\apartment-api';

require_once $base . '/middleware/cors.php';
require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/controllers/AuthController.php';

header('Content-Type: application/json');

apply_cors();
load_env($base . '/.env');

$db         = (new Database())->connect();
$controller = new AuthController($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'register') {
    $controller->register();
} elseif ($method === 'POST' && $action === 'login') {
    $controller->login();
} else {
    require_once $base . '/utils/response.php';
    send_response(false, null, 'Invalid endpoint.', 404);
}
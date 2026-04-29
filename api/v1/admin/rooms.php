<?php
declare(strict_types=1);

$base = 'C:\\xampp\\htdocs\\apartment-api';

require_once $base . '/middleware/cors.php';
require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/middleware/auth.php';
require_once $base . '/controllers/AdminRoomController.php';

header('Content-Type: application/json');

apply_cors();
load_env($base . '/.env');

require_admin();

$db         = (new Database())->connect();
$controller = new AdminRoomController($db);

$method = $_SERVER['REQUEST_METHOD'];

// Extract room ID from URL if present (?id=1)
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($method === 'POST') {
    $controller->addRoom();
} elseif ($method === 'PUT' && $id) {
    $controller->editRoom($id);
} elseif ($method === 'DELETE' && $id) {
    $controller->deleteRoom($id);
} else {
    send_response(false, null, 'Method not allowed.', 405);
}
<?php
declare (strict_types = 1);

$base = 'C:\\xampp\\htdocs\\apartment-api';

require_once $base . '/middleware/cors.php';
require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/controllers/RoomController.php';

header('Content-Type: application/json');

apply_cors();
load_env($base . '/.env');

$db         = (new Database())->connect();
$controller = new RoomController($db);

$method = $_SERVER['REQUEST_METHOD'];

match ($method) {
    'GET'   => $controller->getRooms(),
    default => send_response(false, null, 'Method not allowed.', 405)
};

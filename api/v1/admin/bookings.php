<?php
declare (strict_types = 1);

$base = 'C:\\xampp\\htdocs\\apartment-api';

require_once $base . '/middleware/cors.php';
require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/middleware/auth.php';
require_once $base . '/controllers/BookingController.php';

header('Content-Type: application/json');

apply_cors();
load_env($base . '/.env');

require_admin();

$db         = (new Database())->connect();
$controller = new BookingController($db);

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($method === 'GET') {
    $controller->getAllBookings();
} elseif ($method === 'PUT' && $id) {
    $controller->updateBookingStatus($id);
} else {
    send_response(false, null, 'Method not allowed.', 405);
}

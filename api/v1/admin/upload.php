<?php
declare (strict_types = 1);

$base = 'C:\\xampp\\htdocs\\apartment-api';

require_once $base . '/middleware/cors.php';
require_once $base . '/config/env.php';
require_once $base . '/config/database.php';
require_once $base . '/middleware/auth.php';
require_once $base . '/utils/response.php';

header('Content-Type: application/json');

apply_cors();
load_env($base . '/.env');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_admin();

if ($method !== 'POST') {
    send_response(false, null, 'Method not allowed.', 405);
}

if (! isset($_FILES['image'])) {
    send_response(false, null, 'No image uploaded.', 422);
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp'];
$max_size = 2 * 1024 * 1024; // 2MB

if ($file['error'] !== UPLOAD_ERR_OK) {
    send_response(false, null, 'Upload error.', 422);
}

if (! in_array($file['type'], $allowed, true)) {
    send_response(false, null, 'Only JPG, PNG, and WEBP images are allowed.', 422);
}

if ($file['size'] > $max_size) {
    send_response(false, null, 'Image must be under 2MB.', 422);
}

// Generate unique filename
$ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
$dest     = $base . '/uploads/' . $filename;

if (! move_uploaded_file($file['tmp_name'], $dest)) {
    send_response(false, null, 'Failed to save image.', 500);
}

$image_url = 'http://localhost/apartment-api/uploads/' . $filename;

send_response(true, ['image_url' => $image_url], 'Image uploaded successfully.', 201);

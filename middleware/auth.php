<?php
declare (strict_types = 1);

require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';

function require_auth(): array
{
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? '';

    if (empty($auth) || ! str_starts_with($auth, 'Bearer ')) {
        send_response(false, null, 'Unauthorized.', 401);
    }

    $token   = substr($auth, 7);
    $payload = jwt_verify($token);

    if (! $payload) {
        send_response(false, null, 'Invalid or expired token.', 401);
    }

    return $payload;
}

function require_admin(): array
{
    $payload = require_auth();

    if ($payload['role'] !== 'admin') {
        send_response(false, null, 'Forbidden. Admins only.', 403);
    }

    return $payload;
}

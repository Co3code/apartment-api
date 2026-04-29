<?php
declare (strict_types = 1);

function send_response(bool $success, mixed $data, string $message, int $status_code): void
{
    http_response_code($status_code);
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'message' => $message,
    ]);
    exit;
}

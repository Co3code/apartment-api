<?php
declare(strict_types=1);

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_generate(array $payload): string {
    $secret = $_ENV['JWT_SECRET'] ?? '';

    $header = base64url_encode(json_encode([
        'alg' => 'HS256',
        'typ' => 'JWT'
    ]));

    $payload = base64url_encode(json_encode($payload));

    $signature = base64url_encode(hash_hmac(
        'sha256',
        "{$header}.{$payload}",
        $secret,
        true
    ));

    return "{$header}.{$payload}.{$signature}";
}

function jwt_verify(string $token): array|false {
    $secret = $_ENV['JWT_SECRET'] ?? '';
    $parts  = explode('.', $token);

    if (count($parts) !== 3) {
        return false;
    }

    [$header, $payload, $signature] = $parts;

    $expected_signature = base64url_encode(hash_hmac(
        'sha256',
        "{$header}.{$payload}",
        $secret,
        true
    ));

    // Timing-safe comparison to prevent timing attacks
    if (!hash_equals($expected_signature, $signature)) {
        return false;
    }

    $decoded = json_decode(base64url_decode($payload), true);

    if (!$decoded || !isset($decoded['exp'])) {
        return false;
    }

    // Check token expiry
    if (time() > $decoded['exp']) {
        return false;
    }

    return $decoded;
}
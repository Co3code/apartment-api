<?php
declare (strict_types = 1);

function load_env(string $path): void
{
    if (! file_exists($path)) {
        error_log('.env file not found at: ' . $path);
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        if (! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key           = trim($key);
        $value         = trim($value);

        if (! empty($key)) {
            $_ENV[$key] = $value;
        }
    }
}

<?php
declare (strict_types = 1);

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/jwt.php';

class AuthController
{
    private UserModel $model;

    public function __construct(PDO $db)
    {
        $this->model = new UserModel($db);
    }

    public function register(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $name     = trim($body['name'] ?? '');
        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        // Validate
        if (empty($name) || empty($email) || empty($password)) {
            send_response(false, null, 'All fields are required.', 422);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_response(false, null, 'Invalid email address.', 422);
        }

        if (strlen($password) < 8) {
            send_response(false, null, 'Password must be at least 8 characters.', 422);
        }

        // Check if email already exists
        $existing = $this->model->findByEmail($email);
        if ($existing) {
            send_response(false, null, 'Email already registered.', 409);
        }

        // Hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $id = $this->model->create($name, $email, $hashed);
            send_response(true, ['id' => $id], 'Registration successful.', 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
            send_response(false, null, 'Registration failed.', 500);
        }
    }

    public function login(): void
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $email    = trim($body['email'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($email) || empty($password)) {
            send_response(false, null, 'Email and password are required.', 422);
        }

        $user = $this->model->findByEmail($email);

        if (! $user || ! password_verify($password, $user['password'])) {
            send_response(false, null, 'Invalid email or password.', 401);
        }

        $now = time();

        // Access token payload
        $access_payload = [
            'sub'  => $user['id'],
            'name' => $user['name'],
            'role' => $user['role'],
            'iat'  => $now,
            'exp'  => $now + (int) ($_ENV['JWT_ACCESS_EXPIRY'] ?? 900),
        ];

        // Refresh token payload
        $refresh_payload = [
            'sub' => $user['id'],
            'iat' => $now,
            'exp' => $now + (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 604800),
        ];

        $access_token  = jwt_generate($access_payload);
        $refresh_token = jwt_generate($refresh_payload);

        // Set refresh token in httpOnly cookie
        setcookie('refresh_token', $refresh_token, [
            'expires'  => $now + (int) ($_ENV['JWT_REFRESH_EXPIRY'] ?? 604800),
            'path'     => '/',
            'httponly' => true,
            'secure'   => false, // Set to true in production (HTTPS)
            'samesite' => 'Strict',
        ]);

        send_response(true, [
            'access_token' => $access_token,
            'user'         => [
                'id'   => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'],
            ],
        ], 'Login successful.', 200);
    }
}

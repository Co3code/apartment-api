<?php
declare (strict_types = 1);

class Database
{
    private string $host;
    private string $dbname;
    private string $username;
    private string $password;
    private ?PDO $connection = null;

    public function __construct()
    {
        $this->host     = $_ENV['DB_HOST'] ?? 'localhost';
        $this->dbname   = $_ENV['DB_NAME'] ?? 'apartment_finder';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? '';
    }

    public function connect(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn              = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
                $this->connection = new PDO($dsn, $this->username, $this->password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log($e->getMessage());
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'data'    => null,
                    'message' => 'Database connection failed.',
                ]);
                exit;
            }
        }
        return $this->connection;
    }
}
